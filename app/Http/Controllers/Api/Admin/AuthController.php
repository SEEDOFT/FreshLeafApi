<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginAdminRequest;
use App\Models\Admin;
use App\Models\AdminStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginAdminRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $admin = Admin::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        if ((int) $admin->status_id !== AdminStatus::ACTIVE) {
            return $this->errorResponse('Your account is not active', 403);
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login success');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }
}
