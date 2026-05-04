<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\ReplaceAddressRequest;
use App\Http\Requests\User\Address\StoreAddressRequest;
use App\Http\Requests\User\Address\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {}

    /**
     * List user/vendor addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $addresses = $this->addressService->getUserAddresses($user, $request->integer('per_page', 10));

        return static::successResponse(AddressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    /**
     * Get a specific address.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $address = $user->addresses()->active()->find($id);

        if (! $address) {
            return static::errorResponse('Address not found', 404);
        }

        return static::successResponse(new AddressResource($address), 'Address retrieved successfully');
    }

    /**
     * Create a new address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $address = $this->addressService->createAddress($user, $request->validated());

        return static::successResponse(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Update an existing address.
     */
    public function update(string $id, UpdateAddressRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $address = $user->addresses()->active()->find($id);

        if (! $address) {
            return static::errorResponse('Address not found', 404);
        }

        $address = $this->addressService->updateAddress($address, $request->validated());

        return static::successResponse(new AddressResource($address), 'Address updated successfully');
    }

    /**
     * Replace an existing address.
     */
    public function replace(string $id, ReplaceAddressRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $address = $user->addresses()->active()->find($id);

        if (! $address) {
            return static::errorResponse('Address not found', 404);
        }

        $address = $this->addressService->replaceAddress($address, $request->validated());

        return static::successResponse(new AddressResource($address), 'Address replaced successfully');
    }

    /**
     * Delete an address.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $address = $user->addresses()->active()->find($id);

        if (! $address) {
            return static::errorResponse('Address not found', 404);
        }

        $this->addressService->deleteAddress($address);

        return static::successResponse(message: 'Address deleted successfully');
    }
}
