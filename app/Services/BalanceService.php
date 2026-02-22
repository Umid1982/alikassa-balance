<?php

namespace App\Services;

use App\Repositories\BalanceOperationRepository;
use App\Repositories\BalanceRepository;
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
     * @param int $userId
     * @param string $currency
     * @param string $amount
     * @param string $externalId
     * @return void
     */
    public function depositSeen(int $userId, string $currency, string $amount, string $externalId): void
    {
        DB::transaction(function () use ($userId, $currency, $amount, $externalId) {

            $balance = $this->balanceRepository->getForUpdate($userId, $currency);

            if ($this->operationRepository->existsByExternalId($balance->id, $externalId)) {
                return;
            }

            $this->operationRepository->create([
                'balance_id' => $balance->id,
                'type' => 'deposit',
                'direction' => 'credit',
                'amount' => $amount,
                'status' => 'pending',
                'external_id' => $externalId,
            ]);
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function confirmDeposit(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'confirmed') {
                return;
            }

            $balance = $operation->balance()->lockForUpdate()->first();

            $balance->amount = bcadd($balance->amount, $operation->amount, 18);
            $this->balanceRepository->save($balance);

            $operation->status = 'confirmed';
            $this->operationRepository->save($operation);
        });
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param string $amount
     * @param string $externalId
     * @return void
     */
    public function reserveWithdraw(int $userId, string $currency, string $amount, string $externalId): void
    {
        DB::transaction(function () use ($userId, $currency, $amount, $externalId) {

            $balance = $this->balanceRepository->getForUpdate($userId, $currency);

            if (bccomp($balance->amount, $amount, 18) < 0) {
                throw new RuntimeException('Insufficient funds');
            }

            if ($this->operationRepository->existsByExternalId($balance->id, $externalId)) {
                return;
            }

            $balance->amount = bcsub($balance->amount, $amount, 18);
            $balance->locked_amount = bcadd($balance->locked_amount, $amount, 18);
            $this->balanceRepository->save($balance);

            $this->operationRepository->create([
                'balance_id' => $balance->id,
                'type' => 'withdraw',
                'direction' => 'debit',
                'amount' => $amount,
                'status' => 'reserved',
                'external_id' => $externalId,
            ]);
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function commitWithdraw(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'completed') {
                return;
            }

            $balance = $operation->balance()->lockForUpdate()->first();

            $balance->locked_amount = bcsub($balance->locked_amount, $operation->amount, 18);
            $this->balanceRepository->save($balance);

            $operation->status = 'completed';
            $this->operationRepository->save($operation);
        });
    }

    /**
     * @param string $externalId
     * @return void
     */
    public function cancelWithdraw(string $externalId): void
    {
        DB::transaction(function () use ($externalId) {

            $operation = $this->operationRepository->getByExternalIdForUpdate($externalId);

            if ($operation->status === 'canceled') {
                return;
            }

            $balance = $operation->balance()->lockForUpdate()->first();

            $balance->locked_amount = bcsub($balance->locked_amount, $operation->amount, 18);
            $balance->amount = bcadd($balance->amount, $operation->amount, 18);
            $this->balanceRepository->save($balance);

            $operation->status = 'canceled';
            $this->operationRepository->save($operation);
        });
    }
}
