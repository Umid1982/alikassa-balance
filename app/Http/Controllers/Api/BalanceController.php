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
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    use ApiResponseHelper;

    public function __construct(protected BalanceService $service)
    {
    }

    /**
     * @param DepositSeenRequest $request
     * @return JsonResponse
     */
    public function depositSeen(DepositSeenRequest $request): JsonResponse
    {
        $this->service->depositSeen(...$request->toDTO()->toArray());

        return $this->successResponse(null, 'data', 200, 'Deposit recorded');
    }

    /**
     * @param ExternalIdRequest $request
     * @return JsonResponse
     */
    public function confirmDeposit(ExternalIdRequest $request): JsonResponse
    {
        $this->service->confirmDeposit($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Deposit confirmed');
    }

    /**
     * @param ReserveWithdrawRequest $request
     * @return JsonResponse
     */
    public function reserveWithdraw(ReserveWithdrawRequest $request): JsonResponse
    {
        $this->service->reserveWithdraw(...$request->toDTO()->toArray());

        return $this->successResponse(null, 'data', 200, 'Withdraw reserved');
    }

    /**
     * @param ExternalIdRequest $request
     * @return JsonResponse
     */
    public function commitWithdraw(ExternalIdRequest $request): JsonResponse
    {
        $this->service->commitWithdraw($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Withdraw completed');
    }

    /**
     * @param ExternalIdRequest $request
     * @return JsonResponse
     */
    public function cancelWithdraw(ExternalIdRequest $request): JsonResponse
    {
        $this->service->cancelWithdraw($request->validated('external_id'));

        return $this->successResponse(null, 'data', 200, 'Withdraw canceled');
    }

    /**
     * @param int $userId
     * @param string $currency
     * @return JsonResponse
     */
    public function show(int $userId, string $currency): JsonResponse
    {
        $balance = Balance::query()->where('user_id', $userId)
            ->where('currency', $currency)
            ->firstOrFail();

        return $this->successResponse(
            BalanceResource::make($balance)
        );
    }
}
