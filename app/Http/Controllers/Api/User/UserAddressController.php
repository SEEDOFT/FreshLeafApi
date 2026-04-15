<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\ReplaceAddressRequest;
use App\Http\Requests\User\Address\StoreAddressRequest;
use App\Http\Requests\User\Address\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * List user addresses
     */
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->addresses()
            ->latest()
            ->simplePaginate(request()->integer('per_page', 10));

        return $this->successResponse(AddressResource::collection($addresses));
    }

    /**
     * Create a new user address
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] = "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        }

        $address = Auth::user()->addresses()->create($validatedData);

        return $this->successResponse(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Get a specific user address
     */
    public function show(UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        return $this->successResponse(new AddressResource($userAddress));
    }

    /**
     * Update an existing user address
     */
    public function update(UpdateAddressRequest $request, UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] = "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            unset($validatedData['lat'], $validatedData['long']);
        }

        $userAddress->update($validatedData);

        return $this->successResponse(new AddressResource($userAddress), 'Address updated successfully');
    }

    /**
     * Replace an existing user address
     */
    public function replace(ReplaceAddressRequest $request, UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] = "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            $validatedData['address_map'] = null;
        }

        $userAddress->update($validatedData);

        return $this->successResponse(new AddressResource($userAddress), 'Address replaced successfully');
    }

    /**
     * Delete a user address
     */
    public function destroy(UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $userAddress->delete();

        return $this->successResponse(message: 'Address deleted successfully');
    }
}
