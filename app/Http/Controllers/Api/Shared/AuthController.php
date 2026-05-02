<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UpdatePasswordRequest;
use App\Http\Requests\User\Auth\VerifyPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Verify the authenticated user's password.
     */
    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('auth.verifyPassword');

        if (! Hash::check($request->string('password')->toString(), $user->password)) {
            return static::errorResponse('Invalid password', 401);
        }

        return static::successResponse(message: 'Password verified');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('auth.updatePassword');

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        return static::successResponse(message: 'Password updated');
    }

    /**
     * Log the user out (Revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $user->currentAccessToken()->delete();

        return static::successResponse(message: 'Tokens Revoked');
    }
}
