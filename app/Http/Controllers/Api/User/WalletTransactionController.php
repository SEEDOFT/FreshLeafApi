<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WalletTransaction\StoreWalletTransactionRequest;
use App\Http\Requests\User\WalletTransaction\UpdateWalletTransactionRequest;
use App\Http\Resources\User\WalletTransactionResource;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    /** @var list<string> */
    private const array RELATIONSHIPS = [
        'type',
        'status',
    ];

    /**
     * Display a listing of the user's wallet transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $query = WalletTransaction::query()
            ->whereHas(
                'wallet',
                static fn (Builder $wallet): Builder => $wallet->where('user_id', $user->id)
            )
            ->with(self::RELATIONSHIPS)
            ->orderByDesc('id');

        if ($request->has('wallet_id')) {
            $query->where('wallet_id', $request->integer('wallet_id'));
        }

        $transactions = $query->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            WalletTransactionResource::collection($transactions),
            __('api.wallet_transaction.transactions_retrieved')
        );
    }

    /**
     * Store a newly created wallet transaction in storage.
     */
    public function store(StoreWalletTransactionRequest $request): JsonResponse
    {
        /** @var WalletTransaction $transaction */
        $transaction = DB::transaction(
            static function () use ($request): WalletTransaction {
                $validatedData = $request->validated();

                $transaction = WalletTransaction::create([
                    'wallet_id' => $validatedData['wallet_id'],
                    'wallet_transaction_type_id' => $validatedData['wallet_transaction_type_id'],
                    'amount' => $validatedData['amount'],
                    'currency' => $validatedData['currency'],
                    'description' => $validatedData['description'],
                    'payment_method_id' => $validatedData['payment_method_id'],
                    'reference_number' => $validatedData['reference_number'],
                    'transaction_date' => $validatedData['transaction_date'],
                    'wallet_transaction_status_id' => $validatedData['wallet_transaction_status_id'],
                ]);

                $transaction->recordHistory();

                return $transaction;
            });

        return static::successResponse(
            new WalletTransactionResource($transaction->load(self::RELATIONSHIPS)),
            __('api.wallet_transaction.created'),
            201
        );
    }

    /**
     * Display the specified wallet transaction.
     */
    public function show(string $id): JsonResponse
    {
        $transaction = WalletTransaction::with(['wallet', ...self::RELATIONSHIPS])
            ->find((int) $id);

        if (! $transaction) {
            abort(404, __('api.wallet_transaction.not_found'));
        }

        return static::successResponse(
            new WalletTransactionResource($transaction),
            __('api.wallet_transaction.retrieved')
        );
    }

    /**
     * Update the specified wallet transaction in storage.
     */
    public function update(UpdateWalletTransactionRequest $request, string $id): JsonResponse
    {
        $transaction = WalletTransaction::find((int) $id);

        if (! $transaction) {
            abort(404, __('api.wallet_transaction.not_found'));
        }

        $user = $this->authenticatedUser($request);

        $oldStatusId = $transaction->wallet_transaction_status_id;

        return DB::transaction(function () use ($request, $transaction): JsonResponse {
            $transaction->update($request->validated());

            if ($transaction->wasChanged()) {
                $transaction->recordHistory();
            }

            return static::successResponse(
                new WalletTransactionResource($transaction->load(self::RELATIONSHIPS)),
                __('api.wallet_transaction.updated')
            );
        });
    }

    /**
     * Remove the specified wallet transaction from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $transaction = WalletTransaction::find((int) $id);

        if (! $transaction) {
            abort(404, __('api.wallet_transaction.not_found'));
        }

        $transaction->delete();

        return static::successResponse(message: __('api.wallet_transaction.deleted'));
    }
}
