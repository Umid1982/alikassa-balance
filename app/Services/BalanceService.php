<?php

namespace App\Services;

use App\DTOs\BalanceOperationDTO;
use App\Models\Balance;
use App\Models\BalanceOperation;
use App\Repositories\BalanceOperationRepository;
use App\Repositories\BalanceRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BalanceService
{
    public function __construct(
        protected readonly BalanceRepository          $balanceRepository,
        protected readonly BalanceOperationRepository $operationRepository,
    )
    {
    }

    /**
     * @param BalanceOperationDTO $dto
     * @return void
     */
    public function depositSeen(BalanceOperationDTO $dto): void
    {
        DB::transaction(function () use ($dto) {

            $balance = $this->balanceRepository
                ->getForUpdate($dto->userId, $dto->currency);

            if ($this->operationRepository
                ->existsByExternalId($balance->id, $dto->externalId)) {
                return;
            }

            $this->operationRepository->createDeposit($balance->id, $dto);
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function confirmDeposit(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository
                ->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'confirmed') {
                return;
            }

            $balance = $this->balanceRepository
                ->getByIdForUpdate($operation->balance_id);

            $this->increaseBalance($balance, $operation->amount);

            $this->balanceRepository->save($balance);
            $this->updateOperationStatus($operation, 'confirmed');
        });
    }

    /**
     * @param BalanceOperationDTO $dto
     * @return void
     */
    public function reserveWithdraw(BalanceOperationDTO $dto): void
    {
        DB::transaction(function () use ($dto) {

            $balance = $this->balanceRepository
                ->getForUpdate($dto->userId, $dto->currency);

            $this->ensureSufficientFunds($balance->amount, $dto->amount);

            if ($this->operationRepository
                ->existsByExternalId($balance->id, $dto->externalId)) {
                return;
            }

            $this->lockFunds($balance, $dto->amount);

            $this->balanceRepository->save($balance);

            $this->operationRepository
                ->createWithdrawReservation($balance->id, $dto);
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function commitWithdraw(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository
                ->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'completed') {
                return;
            }

            $balance = $this->balanceRepository
                ->getByIdForUpdate($operation->balance_id);

            $this->releaseLockedFunds($balance, $operation->amount);

            $this->balanceRepository->save($balance);
            $this->updateOperationStatus($operation, 'completed');
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function cancelWithdraw(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository
                ->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'canceled') {
                return;
            }

            $balance = $this->balanceRepository
                ->getByIdForUpdate($operation->balance_id);

            $this->returnLockedFunds($balance, $operation->amount);

            $this->balanceRepository->save($balance);
            $this->updateOperationStatus($operation, 'canceled');
        });
    }

    /**
     * @param int $userId
     * @param string $currency
     * @return Balance
     */
    public function getBalance(int $userId, string $currency): Balance
    {
        return $this->balanceRepository->getByUserAndCurrency($userId, $currency);
    }

    /**
     * @param Balance $balance
     * @param string $amount
     * @return void
     */
    private function increaseBalance(Balance $balance, string $amount): void
    {
        $balance->amount = bcadd($balance->amount, $amount, 18);
    }

    /**
     * @param string $available
     * @param string $required
     * @return void
     */
    private function ensureSufficientFunds(string $available, string $required): void
    {
        if (bccomp($available, $required, 18) < 0) {
            throw new RuntimeException('Insufficient funds');
        }
    }

    /**
     * @param BalanceOperation $operation
     * @param string $status
     * @return void
     */
    private function updateOperationStatus(BalanceOperation $operation, string $status): void
    {
        $operation->status = $status;
        $this->operationRepository->save($operation);
    }

    /**
     * @param Balance $balance
     * @param string $amount
     * @return void
     */
    private function lockFunds(Balance $balance, string $amount): void
    {
        $balance->amount = bcsub($balance->amount, $amount, 18);
        $balance->locked_amount = bcadd($balance->locked_amount, $amount, 18);
    }

    /**
     * @param Balance $balance
     * @param string $amount
     * @return void
     */
    private function releaseLockedFunds(Balance $balance, string $amount): void
    {
        $balance->locked_amount = bcsub($balance->locked_amount, $amount, 18);
    }

    /**
     * @param Balance $balance
     * @param string $amount
     * @return void
     */
    private function returnLockedFunds(Balance $balance, string $amount): void
    {
        $balance->locked_amount = bcsub($balance->locked_amount, $amount, 18);
        $balance->amount = bcadd($balance->amount, $amount, 18);
    }
}
