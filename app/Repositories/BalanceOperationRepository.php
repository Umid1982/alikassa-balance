<?php

namespace App\Repositories;

use App\DTOs\BalanceOperationDTO;
use App\Models\BalanceOperation;

class BalanceOperationRepository
{
    /**
     * @param int $balanceId
     * @param string $externalId
     * @return bool
     */
    public function existsByExternalId(int $balanceId, string $externalId): bool
    {
        return BalanceOperation::query()->where('balance_id', $balanceId)
            ->where('external_id', $externalId)
            ->exists();
    }

    /**
     * @param string $externalId
     * @return BalanceOperation
     */
    public function getByExternalIdForUpdate(string $externalId): BalanceOperation
    {
        return BalanceOperation::query()->where('external_id', $externalId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @param int $balanceId
     * @param BalanceOperationDTO $dto
     * @return mixed
     */
    public function createDeposit(int $balanceId, BalanceOperationDTO $dto): mixed
    {
        return BalanceOperation::query()->create([
            'balance_id' => $balanceId,
            'type' => 'deposit',
            'direction' => 'credit',
            'amount' => $dto->amount,
            'status' => 'pending',
            'external_id' => $dto->externalId,
        ]);
    }

    /**
     * @param BalanceOperation $operation
     * @return void
     */
    public function save(BalanceOperation $operation): void
    {
        $operation->save();
    }

    /**
     * @param int $balanceId
     * @param BalanceOperationDTO $dto
     * @return mixed
     */
    public function createWithdrawReservation(int $balanceId, BalanceOperationDTO $dto): mixed
    {
        return BalanceOperation::query()->create([
            'balance_id' => $balanceId,
            'type' => 'withdraw',
            'direction' => 'debit',
            'amount' => $dto->amount,
            'status' => 'reserved',
            'external_id' => $dto->externalId,
        ]);
    }
}
