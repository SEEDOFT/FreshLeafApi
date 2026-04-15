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
     * Vendor registration
     */
    public function register(RegisterVendorRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $nameParts = preg_split('/\s+/', trim($validatedData['name'])) ?: [];
        $firstName = $nameParts[0] ?? $validatedData['name'];
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Vendor';

        $vendor = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $validatedData['contact_phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        $vendor->vendorProfile()->create([
            'business_name' => $validatedData['business_name'],
            'contact_phone' => $validatedData['contact_phone'],
            'city' => $validatedData['city'],
            'province' => $validatedData['province'],
            'address' => $validatedData['address'],
            'is_verified' => false,
            'meta' => null,
        ]);

        return static::successResponse([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ], 'Vendor registration submitted. Waiting for super admin approval.', 201);
    }

    /**
     * Vendor Login
     */
    public function login(LoginVendorRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $lookup = $validatedData['email'] ?? $validatedData['phone_number'];

        /** @var User|null $vendor */
        $vendor = User::query()
            ->ofType(UserType::VENDOR)
            ->where(
                filter_var($lookup, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number',
                $lookup
            )
            ->first();

        if (! $vendor || ! Hash::check($validatedData['password'], $vendor->password)) {
            return static::errorResponse('Invalid login details', 401);
        }

        if ($vendor->user_status_id !== UserStatus::ACTIVE) {
            return static::errorResponse('Your account is pending approval', 403);
        }

        $token = $vendor->createToken('vendor_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Vendor login success');
    }

    /**
     * Vendor logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return static::successResponse(message: 'Tokens Revoked');
    }
}
