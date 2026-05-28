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
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if ($profile->hasPin()) {
            abort(422, __('api.pin.already_set'));
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.set_success'));
    }

    /**
     * Update the user's existing PIN.
     */
    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if (! $profile->verifyPin($validatedData['current_pin'])) {
            abort(401, __('api.pin.invalid_current_pin'));
        }

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.updated_success'));
    }

    /**
     * Verify the user's PIN for authentication purposes.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validated();
        $profile = $user->userProfile;

        if (! $profile->hasPin()) {
            abort(422, __('api.pin.not_set'));
        }

        if (! $profile->verifyPin($validatedData['pin'])) {
            abort(401, __('api.pin.invalid_pin'));
        }

        return static::successResponse(message: __('api.pin.verified'));
    }

    /**
     * Reset the user's PIN without requiring the current PIN
     * (e.g., for forgotten PIN).
     */
    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validated();
        $profile = $user->userProfile;

        $profile->setPin($validatedData['pin']);

        return static::successResponse(message: __('api.pin.reset_success'));
    }
}
