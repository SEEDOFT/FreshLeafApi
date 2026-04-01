<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'image' => 'user.png',
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'User registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
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
            'user' => new UserResource($user),
        ], 'Login success');
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }

    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid password', 401);
        }

        return $this->successResponse(message: 'Password verified');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse(message: 'Password updated');
    }
}
