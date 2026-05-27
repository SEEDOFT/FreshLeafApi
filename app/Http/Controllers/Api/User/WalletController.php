<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\WalletHistoryResource;
use App\Http\Resources\User\WalletResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display a listing of the user's wallets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallets = $this->walletService
            ->getUserWallets(
                $user,
                $request->integer('per_page', 10)
            );

        return static::successResponse(
            WalletResource::collection($wallets),
            __('api.wallet.wallets_retrieved')
        );
    }

    /**
     * Display the specified wallet.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallet = $user->wallets()->with('currency')->find((int) $id);

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        return static::successResponse(
            new WalletResource($wallet),
            __('api.wallet.retrieved')
        );
    }

    /**
     * Display the specified wallet history.
     */
    public function history(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallet = $user->wallets()->with('currency')->find((int) $id);

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        $histories = $this->walletService
            ->getWalletHistory(
                $wallet,
                $request->integer('per_page', 10)
            );

        return static::successResponse(
            WalletHistoryResource::collection($histories),
            __('api.wallet.history_retrieved')
        );
    }
}
