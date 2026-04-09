<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Vendor\StoreVendorRegistrationRequest;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    public function register(StoreVendorRegistrationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        DB::transaction(function () use ($validatedData): void {
            $user = User::query()->create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'phone_number' => $validatedData['phone_number'],
                'image' => 'user.png',
                'user_status_id' => UserStatus::PENDING,
                'user_type_id' => UserType::VENDOR,
                'password' => Hash::make($validatedData['password']),
            ]);

            VendorProfile::query()->create([
                'user_id' => $user->id,
                'business_name' => $validatedData['business_name'],
                'contact_phone' => $validatedData['phone_number'],
                'city' => $validatedData['city'],
                'province' => $validatedData['province'],
                'address' => $validatedData['address'],
                'is_verified' => false,
            ]);
        });

        return $this->successResponse([], 'Vendor registration submitted. Waiting for super admin approval.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = User::query()
            ->where('phone_number', $validatedData['phone_number'])
            ->ofType(UserType::VENDOR)
            ->active()
            ->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        $token = $user->createToken('vendor_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Vendor login success');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'user_type_id' => $user->user_type_id,
            'user_status_id' => $user->user_status_id,
        ], 'Vendor access granted');
    }

    public function overview(): JsonResponse
    {
        return $this->successResponse([
            'suppliers_total' => Supplier::query()->count(),
            'product_categories_total' => ProductCategory::query()->count(),
            'products_total' => Product::query()->count(),
            'product_variants_total' => ProductVariant::query()->count(),
        ], 'Vendor overview loaded');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = VendorProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => trim($user->first_name.' '.$user->last_name).' Vendor',
                'contact_phone' => $user->phone_number,
                'city' => null,
                'province' => null,
                'address' => null,
                'is_verified' => false,
                'meta' => null,
            ]
        );

        return $this->successResponse($this->profilePayload($profile), 'Vendor profile loaded');
    }

    public function updateProfile(UpdateVendorProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $profile = VendorProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $validated['business_name'],
                'contact_phone' => $validated['contact_phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'province' => $validated['province'] ?? null,
                'address' => $validated['address'] ?? null,
                'meta' => $validated['meta'] ?? null,
            ]
        );

        return $this->successResponse($this->profilePayload($profile), 'Vendor profile updated');
    }

    private function profilePayload(VendorProfile $profile): array
    {
        return [
            'business_name' => $profile->business_name,
            'contact_phone' => $profile->contact_phone,
            'city' => $profile->city,
            'province' => $profile->province,
            'address' => $profile->address,
            'is_verified' => $profile->is_verified,
            'meta' => $profile->meta,
            'updated_at' => optional($profile->updated_at)->toIso8601String(),
        ];
    }
}
