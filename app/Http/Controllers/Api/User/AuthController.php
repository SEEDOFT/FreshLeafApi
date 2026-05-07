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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $password = $validatedData['password'];
        if (! \is_string($password)) {
            return static::errorTranslated('auth.invalid_password_format');
        }

        if (! $user || ! \is_string($user->password) || ! Hash::check($password, $user->password)) {
            return static::errorTranslated('auth.login_failed', [], 401);
        }

        if (! $user->isActive()) {
            return static::errorTranslated('auth.account_not_active', [], 403);
        }

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successTranslated('auth.login_success', [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User $user */
        $user = DB::transaction(static function () use ($validatedData): User {
            $password = $validatedData['password'];
            if (! \is_string($password)) {
                throw new \Exception('Invalid password format');
            }

            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'] ?? null,
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($password),
                'user_type_id' => UserType::USER,
                'user_status_id' => UserStatus::ACTIVE,
            ]);

            $user->userProfile()->create([
                'preferred_language' => 'en',
            ]);

            $user->ensureDefaultWallets();

            return $user;
        });

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successTranslated('auth.register_success', [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * User Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $user->tokens()->delete();

        return static::successTranslated('auth.tokens_revoked');
    }

    /**
     * Verify user's current password
     */
    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $password = $validatedData['password'];
        if (! \is_string($password)) {
            return static::errorTranslated('auth.invalid_password_format');
        }

        if (! \is_string($user->password) || ! Hash::check($password, $user->password)) {
            return static::errorTranslated('auth.invalid_password', [], 401);
        }

        return static::successTranslated('auth.password_verified');
    }

    /**
     * Update user's password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $password = $validatedData['password'];
        if (! \is_string($password)) {
            return static::errorTranslated('auth.invalid_password_format');
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        return static::successTranslated('auth.password_updated');
    }
}
