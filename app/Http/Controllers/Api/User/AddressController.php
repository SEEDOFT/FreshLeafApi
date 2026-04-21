<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\ReplaceAddressRequest;
use App\Http\Requests\User\Address\StoreAddressRequest;
use App\Http\Requests\User\Address\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\Address;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    /**
     * List user addresses
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [Address::class, UserType::USER]);

        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $addresses = $user->addresses()
            ->latest()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(AddressResource::collection($addresses));
    }

    /**
     * Create a new user address
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        Gate::authorize('create', [Address::class, UserType::USER]);

        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        }

        $address = $user->addresses()->create([
            'user_id' => $user->id,
            'label' => $validatedData['label'],
            'recipient_name' => $validatedData['recipient_name'],
            'phone' => $validatedData['phone'],
            'address_line_1' => $validatedData['address_line_1'],
            'address_line_2' => $validatedData['address_line_2'] ?? null,
            'city' => $validatedData['city'],
            'province' => $validatedData['province'],
            'postal_code' => $validatedData['postal_code'],
            'lat' => $validatedData['lat'],
            'long' => $validatedData['long'],
            'address_map' => $validatedData['address_map'],
        ]);

        return static::successResponse(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Get a specific user address
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $userAddress = Address::query()->find($id);

        if (! $userAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('view', [$userAddress, UserType::USER]);

        return static::successResponse(new AddressResource($userAddress), 'Address retrieved successfully');
    }

    /**
     * Update an existing user address
     */
    public function update(string $id, UpdateAddressRequest $request): JsonResponse
    {
        $userAddress = Address::query()->find($id);

        if (! $userAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('update', [$userAddress, UserType::USER]);

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            unset($validatedData['lat'], $validatedData['long']);
        }

        if (isset($validatedData['label'])) {
            $validatedData['label'] = strtoupper($validatedData['label']);
        }

        $userAddress->update($validatedData);

        return static::successResponse(new AddressResource($userAddress), 'Address updated successfully');
    }

    /**
     * Replace an existing user address
     */
    public function replace(string $id, ReplaceAddressRequest $request): JsonResponse
    {
        $userAddress = Address::query()->find($id);

        if (! $userAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('update', [$userAddress, UserType::USER]);

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            $validatedData['address_map'] = null;
        }

        $userAddress->update($validatedData);

        return static::successResponse(new AddressResource($userAddress), 'Address replaced successfully');
    }

    /**
     * Delete a user address
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $userAddress = Address::query()->find($id);

        if (! $userAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('delete', [$userAddress, UserType::USER]);

        $userAddress->delete();

        return static::successResponse(message: 'Address deleted successfully');
    }
}
