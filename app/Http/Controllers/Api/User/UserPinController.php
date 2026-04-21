<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Pin\SetPinRequest;
use App\Http\Requests\User\Pin\UpdatePinRequest;
use App\Http\Requests\User\Pin\VerifyPinRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

class UserPinController extends Controller
{
    /**
     * Set a new PIN for the user.
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $validatedData = $request->validated();
        $profile = UserProfile::firstOrCreateForUser($user);

        if ($profile->hasPin()) {
            return static::errorResponse('PIN already set. Use update endpoint to change it.', 422);
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: 'PIN set successfully');
    }

    /**
     * Update the user's existing PIN.
     */
    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if ($profile === null || ! $profile->verifyPin($validatedData['current_pin'])) {
            return static::errorResponse('Invalid current PIN', 401);
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: 'PIN updated successfully');
    }

    /**
     * Verify the user's PIN for authentication purposes.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if ($profile === null || ! $profile->hasPin()) {
            return static::errorResponse('PIN not set', 422);
        }

        if (! $profile->verifyPin($validatedData['pin'])) {
            return static::errorResponse('Invalid PIN', 401);
        }

        return static::successResponse(message: 'PIN verified');
    }

    /**
     * Reset the user's PIN without requiring the current PIN (e.g., for
     * forgotten PIN).
     */
    public function resetPin(SetPinRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $validatedData = $request->validated();
        $profile = UserProfile::firstOrCreateForUser($user);

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: 'PIN reset successfully');
    }
}
