<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->addresses()->latest()->get();

        return $this->successResponse($addresses);
    }

    /**
     * Store a newly created address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = Auth::user()->addresses()->create($request->validated());

        return $this->successResponse($address, 'Address created successfully', 201);
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        return $this->successResponse($address);
    }

    /**
     * Update the specified address.
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $address->fill($request->validated());
        $address->save();

        return $this->successResponse($address, 'Address updated successfully');
    }

    /**
     * Remove the specified address (soft delete).
     */
    public function destroy(Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $address->delete();

        return $this->successResponse([], 'Address deleted successfully');
    }
}
