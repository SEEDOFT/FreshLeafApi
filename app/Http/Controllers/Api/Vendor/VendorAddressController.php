<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\ReplaceAddressRequest;
use App\Http\Requests\User\Address\StoreAddressRequest;
use App\Http\Requests\User\Address\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\Address;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;

class VendorAddressController extends Controller
{
    public function index(): JsonResponse
    {
        $profile = $this->currentVendorProfile();

        $addresses = $profile->addresses()
            ->latest()
            ->simplePaginate(\request()->integer('per_page', 10));

        return $this->successResponse(AddressResource::collection($addresses));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        }

        $address = $this->currentVendorProfile()->addresses()->create($validatedData);

        return $this->successResponse(
            new AddressResource($address),
            'Address created successfully',
            201
        );
    }

    public function show(Address $address): JsonResponse
    {
        if (! $this->isOwnedByCurrentVendor($address)) {
            return $this->errorResponse('Address not found', 404);
        }

        return $this->successResponse(new AddressResource($address));
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if (! $this->isOwnedByCurrentVendor($address)) {
            return $this->errorResponse('Address not found', 404);
        }

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            unset($validatedData['lat'], $validatedData['long']);
        }

        $address->update($validatedData);

        return $this->successResponse(
            new AddressResource($address),
            'Address updated successfully'
        );
    }

    public function replace(ReplaceAddressRequest $request, Address $address): JsonResponse
    {
        if (! $this->isOwnedByCurrentVendor($address)) {
            return $this->errorResponse('Address not found', 404);
        }

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            $validatedData['address_map'] = null;
        }

        $address->update($validatedData);

        return $this->successResponse(
            new AddressResource($address),
            'Address replaced successfully'
        );
    }

    public function destroy(Address $address): JsonResponse
    {
        if (! $this->isOwnedByCurrentVendor($address)) {
            return $this->errorResponse('Address not found', 404);
        }

        $address->delete();

        return $this->successResponse(message: 'Address deleted successfully');
    }

    private function currentVendor(): User
    {
        /** @var User $vendor */
        $vendor = \request()->user();

        return $vendor;
    }

    private function currentVendorProfile(): VendorProfile
    {
        $profile = $this->currentVendor()->vendorProfile()->firstOrCreate([
            'user_id' => $this->currentVendor()->id,
        ]);

        $profile->ensureDefaultWallets();

        return $profile;
    }

    private function isOwnedByCurrentVendor(Address $address): bool
    {
        $profileId = $this->currentVendorProfile()->id;

        return $address->addressable_type === VendorProfile::class
            && (int) $address->addressable_id === (int) $profileId;
    }
}
