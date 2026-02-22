<?php

use App\Http\Controllers\Api\BalanceController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('balance')
    ->group(function () {
        Route::post('deposit-seen', [BalanceController::class, 'depositSeen']);
        Route::post('deposit-confirm', [BalanceController::class, 'confirmDeposit']);
        Route::post('withdraw-reserve', [BalanceController::class, 'reserveWithdraw']);
        Route::post('withdraw-commit', [BalanceController::class, 'commitWithdraw']);
        Route::post('withdraw-cancel', [BalanceController::class, 'cancelWithdraw']);
        Route::get('{userId}/{currency}', [BalanceController::class, 'show']);
    });
