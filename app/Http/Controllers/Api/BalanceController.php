<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Balance\DepositSeenRequest;
use App\Http\Requests\Balance\ExternalIdRequest;
use App\Http\Requests\Balance\ReserveWithdrawRequest;
use App\Http\Resources\BalanceResource;
use App\Http\Traits\ApiResponseHelper;
use App\Models\Balance;
use App\Services\BalanceService;

class BalanceController extends Controller
{
    use ApiResponseHelper;

    public function __construct(protected BalanceService $service)
    {
    }

    public function depositSeen(DepositSeenRequest $request)
    {
        $this->service->depositSeen(...$request->toDTO()->toArray());

        return $this->successResponse(null, 'data', 200, 'Deposit recorded');
    }

    public function confirmDeposit(ExternalIdRequest $request)
    {
        $this->service->confirmDeposit($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Deposit confirmed');
    }

    public function reserveWithdraw(ReserveWithdrawRequest $request)
    {
        $this->service->reserveWithdraw(...$request->toDTO()->toArray());

        return $this->successResponse(null, 'data', 200, 'Withdraw reserved');
    }

    public function commitWithdraw(ExternalIdRequest $request)
    {
        $this->service->commitWithdraw($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Withdraw completed');
    }

    public function cancelWithdraw(ExternalIdRequest $request)
    {
        $this->service->cancelWithdraw($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Withdraw canceled');
    }

    public function show(int $userId, string $currency)
    {
        $balance = Balance::query()->where('user_id', $userId)
            ->where('currency', $currency)
            ->firstOrFail();

        return $this->successResponse(
            BalanceResource::make($balance)
        );
    }
}
