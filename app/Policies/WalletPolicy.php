<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use Illuminate\Auth\Access\Response;

class WalletPolicy
{
    /**
     * Determine whether the authenticated user can view wallet list.
     */
    public function viewAny(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can create a wallet.
     */
    public function create(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can view the wallet.
     */
    public function view(User $user, Wallet $wallet, int $expectedType): Response
    {
        return $this->ownsWallet($user, $wallet, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the wallet.
     */
    public function update(User $user, Wallet $wallet, int $expectedType): Response
    {
        return $this->ownsWallet($user, $wallet, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the wallet.
     */
    public function delete(User $user, Wallet $wallet, int $expectedType): Response
    {
        return $this->ownsWallet($user, $wallet, $expectedType);
    }

    private function ownsWallet(
        User $user,
        Wallet $wallet,
        int $expectedType,
    ): Response {
        $typeResponse = $this->validateType($user, $expectedType);
        if ($typeResponse->denied()) {
            return $typeResponse;
        }

        if ((int) $wallet->user_id === (int) $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Wallet not found.');
    }

    private function validateType(User $user, int $expectedType): Response
    {
        if (! \in_array($expectedType, [
            UserType::USER,
            UserType::VENDOR,
            UserType::ADMIN,
        ], true)) {
            return Response::denyAsNotFound('Wallet not found.');
        }

        if ((int) $user->user_type_id === $expectedType) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Wallet not found.');
    }
}
