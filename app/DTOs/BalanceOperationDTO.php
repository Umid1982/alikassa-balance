<?php

namespace App\DTOs;

class BalanceOperationDTO
{
    /**
     * @param int $user_id
     * @param string $currency
     * @param string $amount
     * @param string $external_id
     */
    public function __construct(
        public int    $user_id,
        public string $currency,
        public string $amount,
        public string $external_id,
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'external_id' => $this->external_id,
        ];
    }
}
