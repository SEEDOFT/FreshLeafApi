<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Pin\SetPinRequest;
use App\Http\Requests\User\Pin\UpdatePinRequest;
use App\Http\Requests\User\Pin\VerifyPinRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

class UserPinController extends Controller
{
    /**
     * Set a new PIN for the user.
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();
        $profile = UserProfile::firstOrCreateForUser($user);

        if ($profile->hasPin()) {
            return static::errorTranslated('pin.already_set', [], 422);
        }

        $profile->setPin($validatedData['pin']);

        return static::successTrans('pin.set_success');
    }

    /**
     * Update the user's existing PIN.
     */
    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if ($profile === null || ! $profile->verifyPin($validatedData['current_pin'])) {
            return static::errorTranslated('pin.invalid_current_pin', [], 401);
        }

        $profile->setPin($validatedData['pin']);

        return static::successTrans('pin.updated_success');
    }

    /**
     * Verify the user's PIN for authentication purposes.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if ($profile === null || ! $profile->hasPin()) {
            return static::errorTranslated('pin.not_set', [], 422);
        }

        if (! $profile->verifyPin($validatedData['pin'])) {
            return static::errorTranslated('pin.invalid_pin', [], 401);
        }

        return static::successTrans('pin.verified');
    }

    /**
     * Reset the user's PIN without requiring the current PIN (e.g., for
     * forgotten PIN).
     */
    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();
        $profile = UserProfile::firstOrCreateForUser($user);

        $profile->setPin($validatedData['pin']);

        return static::successTrans('pin.reset_success');
    }
}
