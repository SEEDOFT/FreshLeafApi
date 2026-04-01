<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\ReplaceAddressRequest;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->addresses()
            ->latest()
            ->simplePaginate(request()->integer('per_page', 10));

        return $this->successResponse(AddressResource::collection($addresses));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        }

        $address = Auth::user()->addresses()->create($data);

        return $this->successResponse(new AddressResource($address), 'Address created successfully', 201);
    }

    public function show(Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        return $this->successResponse(new AddressResource($address));
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $data = $request->validated();

        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            unset($data['lat'], $data['long']);
        }

        $address->update($data);

        return $this->successResponse(new AddressResource($address), 'Address updated successfully');
    }

    public function replace(ReplaceAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $data = $request->validated();

        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            $data['address_map'] = null;
        }

        $address->update($data);

        return $this->successResponse(new AddressResource($address), 'Address replaced successfully');
    }

    public function destroy(Address $address): JsonResponse
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $address->delete();

        return $this->successResponse(message: 'Address deleted successfully');
    }
}
