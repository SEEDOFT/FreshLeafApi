<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use App\Models\UserType;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class AddressPolicy
{
    /**
     * Determine whether the authenticated user can view address list.
     */
    public function viewAny(User $user, ?int $expectedType = null): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can create an address.
     */
    public function create(User $user, ?int $expectedType = null): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can view the address.
     */
    public function view(User $user, Address $address, ?int $expectedType = null): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the address.
     */
    public function update(User $user, Address $address, ?int $expectedType = null): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the address.
     */
    public function delete(User $user, Address $address, ?int $expectedType = null): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Check if the address belongs to the user and user type is valid.
     */
    private function ownsAddress(
        User $user,
        Address $address,
        ?int $expectedType,
    ): Response {
        $typeResponse = $this->validateType($user, $expectedType);
        if ($typeResponse->denied()) {
            return $typeResponse;
        }

        if ((int) $address->user_id === (int) $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Address not found.');
    }

    private function validateType(User $user, ?int $expectedType): Response
    {
        // If expectedType is not provided (e.g. from Filament), infer it from the current panel
        if ($expectedType === null) {
            $panel = Filament::getCurrentPanel();

            if (! $panel) {
                return Response::deny();
            }

            $expectedType = match ($panel->getId()) {
                'admin' => UserType::ADMIN,
                'vendor' => UserType::VENDOR,
                default => null,
            };
        }

        if (! \in_array($expectedType, [
            UserType::USER,
            UserType::VENDOR,
            UserType::ADMIN,
        ], true)) {
            return Response::denyAsNotFound('Address not found.');
        }

        if ((int) $user->user_type_id === $expectedType) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Address not found.');
    }
}
