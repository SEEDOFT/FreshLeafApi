<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    /**
     * Show vendor profile
     */
    public function show(Request $request): JsonResponse
    {
        $vendor = $this->authenticatedUser($request);

        Gate::authorize('view', [$vendor, UserType::VENDOR]);

        return $this->successResponse(
            new VendorProfileResource($vendor->loadMissing('vendorProfile')),
            'Vendor profile loaded'
        );
    }

    /**
     * Update vendor profile
     */
    public function update(UpdateVendorProfileRequest $request): JsonResponse
    {
        $vendor = $this->authenticatedUser($request);

        Gate::authorize('update', [$vendor, UserType::VENDOR]);

        $validatedData = $request->validated();

        $profile = $vendor->vendorProfile()->firstOrCreate(['user_id' => $vendor->id]);

        $profile->update([
            'business_name' => $validatedData['business_name'],
            'contact_phone' => $validatedData['contact_phone'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'province' => $validatedData['province'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'meta' => $validatedData['meta'] ?? null,
        ]);

        return $this->successResponse(
            new VendorProfileResource($vendor->fresh()->load('vendorProfile')),
            'Vendor profile updated'
        );
    }
}
