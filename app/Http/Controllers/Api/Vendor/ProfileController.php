<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Vendor $vendor */
        $vendor = $request->user();

        return $this->successResponse(new VendorProfileResource($vendor), 'Vendor profile loaded');
    }

    public function update(UpdateVendorProfileRequest $request): JsonResponse
    {
        /** @var Vendor $vendor */
        $vendor = $request->user();
        $validated = $request->validated();

        $vendor->update([
            'business_name' => $validated['business_name'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'province' => $validated['province'] ?? null,
            'address' => $validated['address'] ?? null,
            'meta' => $validated['meta'] ?? null,
        ]);

        return $this->successResponse(new VendorProfileResource($vendor->fresh()), 'Vendor profile updated');
    }
}
