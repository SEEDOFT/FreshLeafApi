<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\Response;

class AddressPolicy
{
    /**
     * Determine whether the authenticated user can view address list.
     */
    public function viewAny(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can create an address.
     */
    public function create(User $user, int $expectedType): Response
    {
        return $this->validateType($user, $expectedType);
    }

    /**
     * Determine whether the authenticated user can view the address.
     */
    public function view(User $user, Address $address, int $expectedType): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the address.
     */
    public function update(User $user, Address $address, int $expectedType): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the address.
     */
    public function delete(User $user, Address $address, int $expectedType): Response
    {
        return $this->ownsAddress($user, $address, $expectedType);
    }

    /**
     * Check if the address belongs to the user and user type is valid.
     */
    private function ownsAddress(
        User $user,
        Address $address,
        int $expectedType,
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

    private function validateType(User $user, int $expectedType): Response
    {
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
