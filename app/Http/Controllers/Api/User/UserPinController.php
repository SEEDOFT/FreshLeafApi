<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Pin\SetPinRequest;
use App\Http\Requests\User\Pin\UpdatePinRequest;
use App\Http\Requests\User\Pin\VerifyPinRequest;
use Illuminate\Http\JsonResponse;

class UserPinController extends Controller
{
    /**
     * Set a new PIN for the user.
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);
        $profile = $user->userProfile;

        if ($profile->hasPin()) {
            return static::errorResponse(__('api.pin.already_set'), 422);
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.set_success'));
    }

    /**
     * Update the user's existing PIN.
     */
    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);
        $profile = $user->userProfile;

        if (! $profile->verifyPin($validatedData['current_pin'])) {
            return static::errorResponse(__('api.pin.invalid_current_pin'), 401);
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.updated_success'));
    }

    /**
     * Verify the user's PIN for authentication purposes.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);
        $profile = $user->userProfile;

        if (! $profile->hasPin()) {
            return static::errorResponse(__('api.pin.not_set'), 422);
        }

        if (! $profile->verifyPin($validatedData['pin'])) {
            return static::errorResponse(__('api.pin.invalid_pin'), 401);
        }

        return static::successResponse(message: __('api.pin.verified'));
    }

    /**
     * Reset the user's PIN without requiring the current PIN
     * (e.g., for forgotten PIN).
     */
    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);
        $profile = $user->userProfile;

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.reset_success'));
    }
}
