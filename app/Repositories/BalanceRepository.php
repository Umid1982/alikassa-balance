<?php

namespace App\Repositories;

use App\Models\Balance;

class BalanceRepository
{
    /**
     * @param int $userId
     * @param string $currency
     * @return Balance
     */
    public function getForUpdate(int $userId, string $currency): Balance
    {
        return Balance::query()->where('user_id', $userId)
            ->where('currency', $currency)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @param Balance $balance
     * @return void
     */
    public function save(Balance $balance): void
    {
        $balance->save();
    }
}
