<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\WalletHistoryResource;
use App\Http\Resources\Shared\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    /**
     * Display a listing of the user's wallets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('viewAny', [Wallet::class, $user->user_type_id]);

        $wallets = $user->wallets()
            ->with('currency')
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            WalletResource::collection($wallets),
            'Wallets retrieved successfully'
        );
    }

    /**
     * Display the specified wallet.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $wallet = Wallet::with('currency')->find($id);

        if (! $wallet) {
            return static::errorResponse('Wallet not found', 404);
        }

        Gate::authorize('view', [$wallet, $user->user_type_id]);

        return static::successResponse(
            new WalletResource($wallet),
            'Wallet retrieved successfully'
        );
    }

    /**
     * Display the specified wallet history.
     */
    public function history(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $wallet = Wallet::query()->find($id);

        if (! $wallet) {
            return static::errorResponse('Wallet not found', 404);
        }

        Gate::authorize('view', [$wallet, $user->user_type_id]);

        $histories = $wallet->histories()
            ->with('currency')
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            WalletHistoryResource::collection($histories),
            'Wallet history retrieved successfully'
        );
    }
}
