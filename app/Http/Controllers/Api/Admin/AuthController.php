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
    /**
     * Admin Login
     */
    public function login(LoginAdminRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var Admin|null $admin */
        $admin = Admin::where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $admin) {
            return $this->errorResponse('Admin not found', 404);
        }

        if (! Hash::check($validatedData['password'], $admin->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        if ($admin->admin_status_id !== AdminStatus::ACTIVE) {
            return $this->errorResponse('Your account is not active', 403);
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login success');
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse(message: 'Tokens Revoked');
    }
}
