<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordRequest;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone_number' => $validatedData['phone_number'],
            'image' => 'user.png',
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = User::where('phone_number', $validatedData['phone_number'])
            ->where('user_type_id', UserType::CONSUMER)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        if ($user->user_status_id !== UserStatus::ACTIVE) {
            return $this->errorResponse('Your account is not active', 403);
        }

        if ($user->user_type_id !== UserType::CONSUMER) {
            return $this->errorResponse('Only consumers can login here', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login success');
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }

    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        if (! Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid password', 401);
        }

        return $this->successResponse(message: 'Password verified');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validatedData['password']),
        ]);

        return $this->successResponse(message: 'Password updated');
    }
}
