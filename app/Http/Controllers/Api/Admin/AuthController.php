<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginAdminRequest;
use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Admin Login
     */
    public function login(LoginAdminRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User|null $admin */
        $admin = User::ofType(UserType::ADMIN)
            ->where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $admin || ! Hash::check($validatedData['password'], $admin->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        if (! $admin->isActive()) {
            return $this->errorResponse('Admin account is not active', 403);
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login success');
    }

    /**
     * Admin registration (developer-only).
     */
    public function register(RegisterAdminRequest $request): JsonResponse
    {
        $registrationKey = (string) config('auth.admin_registration_key');
        $providedKey = (string) $request->header('X-Admin-Registration-Key');

        if ($registrationKey === '' || ! hash_equals($registrationKey, $providedKey)) {
            return static::errorResponse('Admin registration is disabled', 403);
        }

        $validatedData = $request->validated();

        $admin = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'] ?? null,
            'phone_number' => $validatedData['phone_number'],
            'image' => 'user.png',
            'password' => Hash::make($validatedData['password']),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $admin->adminProfile()->create([
            'department' => $validatedData['department'] ?? null,
            'job_title' => $validatedData['job_title'] ?? null,
            'office_phone' => $validatedData['office_phone'] ?? null,
            'super_admin' => $validatedData['super_admin'] ?? true,
            'permissions' => $validatedData['permissions'] ?? [],
        ]);

        $admin->ensureDefaultWallets();

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return static::successResponse([
            'admin_id' => $admin->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin registered successfully', 201);
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $admin = $this->authenticatedUser($request);
        $admin->currentAccessToken()->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }
}
