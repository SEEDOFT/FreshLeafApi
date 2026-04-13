<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\LoginVendorRequest;
use App\Http\Requests\Vendor\RegisterVendorRequest;
use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
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

        $vendor = Vendor::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
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

        /** @var Vendor|null $vendor */
        $vendor = Vendor::where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $vendor || ! Hash::check($validatedData['password'], $vendor->password)) {
            return static::errorResponse('Invalid login details', 401);
        }

        if ($vendor->vendor_status_id !== VendorStatus::ACTIVE) {
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
