<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use App\Models\WalletTransaction;
use Illuminate\Auth\Access\Response;

class WalletTransactionPolicy
{
    /**
     * Determine whether the authenticated user can view transaction list.
     */
    public function viewAny(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can create a transaction.
     */
    public function create(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can view the transaction.
     */
    public function view(User $user, WalletTransaction $transaction, int $expectedType): Response
    {
        return $this->ownsTransaction($user, $transaction, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the transaction.
     */
    public function update(User $user, WalletTransaction $transaction, int $expectedType): Response
    {
        return $this->ownsTransaction($user, $transaction, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the transaction.
     */
    public function delete(User $user, WalletTransaction $transaction, int $expectedType): Response
    {
        return $this->ownsTransaction($user, $transaction, $expectedType);
    }

    private function ownsTransaction(
        User $user,
        WalletTransaction $transaction,
        int $expectedType,
    ): Response {
        $typeResponse = $this->validateType($user, $expectedType);
        if ($typeResponse->denied()) {
            return $typeResponse;
        }

        $wallet = $transaction->wallet;

        if ($wallet && (int) $wallet->user_id === (int) $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Transaction not found.');
    }

    private function validateType(User $user, int $expectedType): Response
    {
        if (! \in_array($expectedType, [
            UserType::USER,
            UserType::VENDOR,
            UserType::ADMIN,
        ], true)) {
            return Response::denyAsNotFound('Transaction not found.');
        }

        if ((int) $user->user_type_id === $expectedType) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Transaction not found.');
    }
}
