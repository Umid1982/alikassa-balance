<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceOperation extends Model
{
    protected $table = 'balance_operations';
    protected $fillable = [
        'balance_id',
        'type',
        'direction',
        'amount',
        'status',
        'external_id',
    ];

    protected $casts = [
        'amount' => 'string',
    ];

    /**
     * @return BelongsTo
     */
    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class);
    }
}
