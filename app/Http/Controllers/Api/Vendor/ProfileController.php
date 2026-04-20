<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $vendor */
        $vendor = $request->user();
        $vendor->loadMissing('vendorProfile');

        return $this->successResponse(new VendorProfileResource($vendor), 'Vendor profile loaded');
    }

    public function update(UpdateVendorProfileRequest $request): JsonResponse
    {
        /** @var User $vendor */
        $vendor = $request->user();
        $validated = $request->validated();

        $profile = $vendor->vendorProfile()->firstOrCreate(['user_id' => $vendor->id]);
        $profile->ensureDefaultWallets();
        $profile->update([
            'business_name' => $validated['business_name'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'province' => $validated['province'] ?? null,
            'address' => $validated['address'] ?? null,
            'meta' => $validated['meta'] ?? null,
        ]);

        return $this->successResponse(new VendorProfileResource($vendor->fresh()->load('vendorProfile')), 'Vendor profile updated');
    }
}
