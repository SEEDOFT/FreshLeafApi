<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Http\Requests\User\Auth\UpdatePasswordRequest;
use App\Http\Requests\User\Auth\VerifyPasswordRequest;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * User Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User|null $user */
        $user = User::ofType(UserType::USER)
            ->where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return static::errorResponse('Invalid login details', 401);
        }

        if (! $user->isActive()) {
            return static::errorResponse('Your account is not active', 403);
        }

        if (! $user->isType(UserType::USER)) {
            return static::errorResponse('Invalid login details', 401);
        }

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login success');
    }

    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone_number' => $validatedData['phone_number'],
            'image' => 'user.png',
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::USER,
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->userProfile()->create([
            'preferred_language' => 'en',
        ]);

        $user->ensureDefaultWallets();

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * User Logout
     */
    public function logout(): JsonResponse
    {
        \auth()->user()->tokens()->delete();

        return static::successResponse(message: 'Tokens Revoked');
    }

    /**
     * Verify user's current password
     */
    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if (! Hash::check($validatedData['password'], $this->user()->password)) {
            return static::errorResponse('Invalid password', 401);
        }

        return static::successResponse(message: 'Password verified');
    }

    /**
     * Update user's password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $this->user()->update([
            'password' => Hash::make($validatedData['password']),
        ]);

        return static::successResponse(message: 'Password updated');
    }
}
