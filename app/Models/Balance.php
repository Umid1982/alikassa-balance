<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balance extends Model
{
    protected $table = 'balance';
    protected $fillable = [
        'user_id',
        'currency',
        'amount',
    ];

    protected $casts = [
        'amount' => 'string', // чтобы не потерять точность при JSON/логике
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function operations(): HasMany
    {
        return $this->hasMany(BalanceOperation::class);
    }

}
