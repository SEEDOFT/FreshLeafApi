<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\LoginVendorRequest;
use App\Http\Requests\Vendor\RegisterVendorRequest;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Vendor Login
     */
    public function login(LoginVendorRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User|null $vendor */
        $vendor = User::ofType(UserType::VENDOR)
            ->where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $vendor || ! Hash::check($validatedData['password'], $vendor->password)) {
            return static::errorResponse('Invalid login details', 401);
        }

        if (! $vendor->isActive()) {
            return static::errorResponse('Your account is pending approval', 403);
        }

        $token = $vendor->createToken('vendor_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Vendor login success');
    }

    /**
     * Vendor registration
     */
    public function register(RegisterVendorRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $phoneNumber = $validatedData['phone_number'];

        $vendor = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone_number' => $phoneNumber,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        $vendor->vendorProfile()->create([
            'business_name' => $validatedData['business_name'],
            'contact_phone' => $phoneNumber,
            'city' => $validatedData['city'] ?? null,
            'province' => $validatedData['province'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'is_verified' => false,
            'meta' => null,
        ]);

        $vendor->ensureDefaultWallets();

        return static::successResponse([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ], 'Vendor registration submitted. Waiting for super admin approval.', 201);
    }

    /**
     * Vendor logout
     */
    public function logout(Request $request): JsonResponse
    {
        $vendor = $this->authenticatedUser($request);
        $vendor->currentAccessToken()->delete();

        return static::successResponse(message: 'Tokens Revoked');
    }
}
