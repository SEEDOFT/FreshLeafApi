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
    public function register(RegisterVendorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $vendor = Vendor::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
            'business_name' => $validated['business_name'],
            'contact_phone' => $validated['contact_phone'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'address' => $validated['address'],
            'is_verified' => false,
            'meta' => null,
        ]);

        return $this->successResponse([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ], 'Vendor registration submitted. Waiting for super admin approval.', 201);
    }

    public function login(LoginVendorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $vendor = Vendor::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $vendor || ! Hash::check($validated['password'], $vendor->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        if ((int) $vendor->status_id !== VendorStatus::ACTIVE) {
            return $this->errorResponse('Your account is pending approval', 403);
        }

        $token = $vendor->createToken('vendor_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Vendor login success');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }
}
