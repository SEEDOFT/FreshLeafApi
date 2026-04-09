<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\ReplaceAddressRequest;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
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

    public function show(UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        return $this->successResponse(new AddressResource($userAddress));
    }

    public function update(UpdateAddressRequest $request, UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $data = $request->validated();

        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            unset($data['lat'], $data['long']);
        }

        $userAddress->update($data);

        return $this->successResponse(new AddressResource($userAddress), 'Address updated successfully');
    }

    public function replace(ReplaceAddressRequest $request, UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $data = $request->validated();

        if (isset($data['lat'], $data['long'])) {
            $data['address_map'] = "https://www.google.com/maps?q={$data['lat']},{$data['long']}";
        } else {
            $data['address_map'] = null;
        }

        $userAddress->update($data);

        return $this->successResponse(new AddressResource($userAddress), 'Address replaced successfully');
    }

    public function destroy(UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== Auth::id()) {
            return $this->errorResponse('Address not found', 404);
        }

        $userAddress->delete();

        return $this->successResponse(message: 'Address deleted successfully');
    }
}
