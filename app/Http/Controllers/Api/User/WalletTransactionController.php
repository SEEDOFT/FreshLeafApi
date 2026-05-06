<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WalletTransaction\StoreWalletTransactionRequest;
use App\Http\Requests\User\WalletTransaction\UpdateWalletTransactionRequest;
use App\Http\Resources\User\WalletTransactionResource;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the user's wallet transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $query = WalletTransaction::whereHas(
            'wallet', static function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['type', 'status'])
            ->orderByDesc('id');

        if ($request->has('wallet_id')) {
            $query->where('wallet_id', $request->integer('wallet_id'));
        }

        $transactions = $query->simplePaginate($request->integer('per_page', 15));

        return $this->successResponse(
            WalletTransactionResource::collection($transactions),
            'Wallet transactions retrieved successfully'
        );
    }

    /**
     * Store a newly created wallet transaction in storage.
     */
    public function store(StoreWalletTransactionRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return DB::transaction(function () use ($request, $user): JsonResponse {
            $transaction = WalletTransaction::create($request->validated());

            WalletTransactionHistory::create([
                'wallet_transaction_id' => $transaction->id,
                'from_wallet_transaction_status_id' => null,
                'to_wallet_transaction_status_id' => $transaction->wallet_transaction_status_id,
                'changed_by_user_id' => $user->id,
                'note' => 'Transaction initiated',
            ]);

            return $this->successResponse(
                new WalletTransactionResource($transaction->load(['type', 'status'])),
                'Wallet transaction created successfully',
                201
            );
        });
    }

    /**
     * Display the specified wallet transaction.
     */
    public function show(string $id): JsonResponse
    {
        $transaction = WalletTransaction::with(['type', 'status', 'wallet'])
            ->find($id);

        if (! $transaction) {
            return $this->errorResponse('Wallet transaction not found', 404);
        }

        return $this->successResponse(
            new WalletTransactionResource($transaction),
            'Wallet transaction retrieved successfully'
        );
    }

    /**
     * Update the specified wallet transaction in storage.
     */
    public function update(UpdateWalletTransactionRequest $request, string $id): JsonResponse
    {
        $transaction = WalletTransaction::find($id);

        if (! $transaction) {
            return $this->errorResponse('Wallet transaction not found', 404);
        }

        $user = $this->authenticatedUser($request);
        $oldStatusId = $transaction->wallet_transaction_status_id;

        return DB::transaction(function () use ($request, $transaction, $user, $oldStatusId): JsonResponse {
            $transaction->update($request->validated());

            if ($transaction->wasChanged('wallet_transaction_status_id')) {
                WalletTransactionHistory::create([
                    'wallet_transaction_id' => $transaction->id,
                    'from_wallet_transaction_status_id' => $oldStatusId,
                    'to_wallet_transaction_status_id' => $transaction->wallet_transaction_status_id,
                    'changed_by_user_id' => $user->id,
                    'note' => 'Status updated',
                ]);
            }

            return $this->successResponse(
                new WalletTransactionResource($transaction->load(['type', 'status'])),
                'Wallet transaction updated successfully'
            );
        });
    }

    /**
     * Remove the specified wallet transaction from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $transaction = WalletTransaction::find($id);

        if (! $transaction) {
            return $this->errorResponse('Wallet transaction not found', 404);
        }

        $transaction->delete();

        return $this->successResponse([], 'Wallet transaction deleted successfully');
    }
}
