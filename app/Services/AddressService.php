<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;

class AddressService
{
    /**
    /**
     * Get paginated user addresses.
     *
     * @return Paginator<int, Address>
     */
    public function getUserAddresses(User $user, int $perPage): Paginator
    {
        return $user->addresses()
            ->active()
            ->latest()
            ->simplePaginate($perPage);
    }

    /**
     * Create a new address for the user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAddress(User $user, array $data): Address
    {
        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        }

        return $user->addresses()->create(array_merge($data, [
            'user_id' => $user->id,
        ]));
    }

    /**
     * Update an existing address dynamically.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAddress(Address $address, array $data): Address
    {
        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            unset($data['lat'], $data['long']);
        }

        if (isset($data['label'])) {
            $data['label'] = strtoupper($data['label']);
        }

        $address->update($data);

        return $address;
    }

    /**
     * Replace an entire address.
     *
     * @param  array<string, mixed>  $data
     */
    public function replaceAddress(Address $address, array $data): Address
    {
        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            $data['address_map'] = null;
        }

        $address->update($data);

        return $address;
    }

    /**
     * Delete an address.
     */
    public function deleteAddress(Address $address): ?bool
    {
        return $address->delete();
    }
}
