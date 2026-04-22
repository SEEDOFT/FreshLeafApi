<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\ReplaceAddressRequest;
use App\Http\Requests\User\Address\StoreAddressRequest;
use App\Http\Requests\User\Address\UpdateAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Models\Address;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorAddressController extends Controller
{
    /**
     * List vendor addresses
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [Address::class, UserType::VENDOR]);

        $vendor = $this->authenticatedUser($request);

        $addresses = $vendor->addresses()
            ->latest()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(AddressResource::collection($addresses));
    }

    /**
     * Create a new vendor address
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        Gate::authorize('create', [Address::class, UserType::VENDOR]);

        $vendor = $this->authenticatedUser($request);

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        }

        $address = $vendor->addresses()->create([
            'user_id' => $vendor->id,
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
     * Get a specific vendor address
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $vendorAddress = Address::query()->find($id);

        if (! $vendorAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('view', [$vendorAddress, UserType::VENDOR]);

        return static::successResponse(new AddressResource($vendorAddress), 'Address retrieved successfully');
    }

    /**
     * Update an existing vendor address
     */
    public function update(string $id, UpdateAddressRequest $request): JsonResponse
    {
        $vendorAddress = Address::query()->find($id);

        if (! $vendorAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('update', [$vendorAddress, UserType::VENDOR]);

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            unset($validatedData['lat'], $validatedData['long']);
        }

        $vendorAddress->update($validatedData);

        return static::successResponse(new AddressResource($vendorAddress), 'Address updated successfully');
    }

    /**
     * Replace an existing vendor address
     */
    public function replace(string $id, ReplaceAddressRequest $request): JsonResponse
    {
        $vendorAddress = Address::query()->find($id);

        if (! $vendorAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('update', [$vendorAddress, UserType::VENDOR]);

        $validatedData = $request->validated();

        if (isset($validatedData['lat'], $validatedData['long'])) {
            $validatedData['address_map'] =
                "https://www.google.com/maps?q={$validatedData['lat']},{$validatedData['long']}";
        } else {
            $validatedData['address_map'] = null;
        }

        $vendorAddress->update($validatedData);

        return static::successResponse(new AddressResource($vendorAddress), 'Address replaced successfully');
    }

    /**
     * Delete a vendor address
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $vendorAddress = Address::query()->find($id);

        if (! $vendorAddress) {
            return static::errorResponse('Address not found', 404);
        }

        Gate::authorize('delete', [$vendorAddress, UserType::VENDOR]);

        $vendorAddress->delete();

        return static::successResponse(message: 'Address deleted successfully');
    }
}
