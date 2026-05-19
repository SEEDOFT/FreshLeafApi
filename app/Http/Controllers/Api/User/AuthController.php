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
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use function is_string;

class AuthController extends Controller
{
    /**
     * User Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User|null $user */
        $user = User::ofType(UserType::CONSUMER_ID)
            ->where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return static::errorResponse(__('api.auth.login_failed'), 401);
        }

        if (! $user->isActive()) {
            return static::errorResponse(__('api.auth.account_not_active'), 403);
        }

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], __('api.auth.login_success'));
    }

    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var User $user */
        $user = DB::transaction(static function () use ($validatedData): User {
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'] ?? null,
                'email_verified_at' => null,
                'phone_number' => $validatedData['phone_number'],
                'phone_number_verified_at' => null,
                'password' => Hash::make($validatedData['password']),
                'user_type_id' => UserType::CONSUMER_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ]);

            $user->userProfile()->create([
                'locale' => 'km',
                'theme' => 'system',
            ]);

            $user->ensureDefaultWallets();

            $user->ensureDefaultPaymentMethod();

            return $user;
        });

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return static::successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], __('api.auth.register_success'), 201);
    }

    /**
     * Admin registration (for testing)
     */
    public function registerForAdmin(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(static function (Builder $query): void {
                        $query->where('user_type_id', UserType::ADMIN_ID)
                            ->whereNull('deleted_at');
                    }),
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'starts_with:+855',
                Rule::unique('users', 'phone_number')
                    ->where(static function (Builder $query): void {
                        $query->where('user_type_id', UserType::ADMIN_ID)
                            ->whereNull('deleted_at');
                    }),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(static function () use ($validatedData): void {
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'] ?? null,
                'email_verified_at' => null,
                'phone_number' => $validatedData['phone_number'],
                'phone_number_verified_at' => null,
                'password' => Hash::make($validatedData['password']),
                'user_type_id' => UserType::ADMIN_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ]);

            $user->adminProfile()->create([
                'locale' => 'km',
                'theme' => 'system',
                'super_admin' => true,
            ]);

            $user->ensureDefaultWallets();
        });

        return static::successResponse(message: __('api.auth.register_success'), code: 201);
    }

    /**
     * User Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $user->tokens()->delete();

        return static::successResponse(message: __('api.auth.tokens_revoked'));
    }

    /**
     * Verify user's current password
     */
    public function verifyPassword(VerifyPasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        if (
            ! is_string($user->password) ||
            ! Hash::check($validatedData['password'], $user->password)
        ) {
            return static::errorResponse(__('api.auth.invalid_password'), 401);
        }

        return static::successResponse(message: __('api.auth.password_verified'));
    }

    /**
     * Update user's password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $user->update([
            'password' => Hash::make($validatedData['password']),
        ]);

        return static::successResponse(message: __('api.auth.password_updated'));
    }
}
