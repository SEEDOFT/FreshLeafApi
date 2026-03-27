<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'image' => 'customer.png',
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
            return $this->errorResponse('Invalid login details', 401);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return $this->errorResponse('Invalid login details', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->successResponse([], 'Tokens Revoked');
    }
}
