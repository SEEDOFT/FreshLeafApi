<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\WalletHistoryResource;
use App\Http\Resources\Shared\WalletResource;
use App\Models\UserType;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    /**
     * Display a listing of the admin wallets.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [Wallet::class, UserType::ADMIN]);

        $admin = $this->authenticatedUser($request);

        $wallets = $admin->wallets()
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
    public function show(string $id): JsonResponse
    {
        $wallet = Wallet::query()
            ->with('currency')
            ->find($id);

        if (! $wallet) {
            return static::errorResponse('Wallet not found', 404);
        }

        Gate::authorize('view', [$wallet, UserType::ADMIN]);

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
        $wallet = Wallet::query()->find($id);

        if (! $wallet) {
            return static::errorResponse('Wallet not found', 404);
        }

        Gate::authorize('view', [$wallet, UserType::ADMIN]);

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
