<?php

namespace App\Repositories;

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
     * @param array $data
     * @return BalanceOperation
     */
    public function create(array $data): BalanceOperation
    {
        return BalanceOperation::query()->create($data);
    }

    /**
     * @param BalanceOperation $operation
     * @return void
     */
    public function save(BalanceOperation $operation): void
    {
        $operation->save();
    }
}
