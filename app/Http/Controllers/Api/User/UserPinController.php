<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Pin\SetPinRequest;
use App\Http\Requests\User\Pin\UpdatePinRequest;
use App\Http\Requests\User\Pin\VerifyPinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserPinController extends Controller
{
    /**
     * Set a new PIN for the user.
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->user();
        $profile = $user->userProfile()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'preferred_language' => 'en',
        ]);

        if ($profile->pin) {
            return static::errorResponse('PIN already set. Use update endpoint to change it.', 422);
        }

        $profile->update(['pin' => Hash::make($validatedData['pin'])]);

        return static::successResponse(message: 'PIN set successfully');
    }

    /**
     * Update the user's existing PIN.
     */
    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->user();
        $profile = $user->userProfile;

        if ($profile === null || ! Hash::check($validatedData['current_pin'], $profile->pin)) {
            return static::errorResponse('Invalid current PIN', 401);
        }

        $profile->update(['pin' => Hash::make($validatedData['pin'])]);

        return static::successResponse(message: 'PIN updated successfully');
    }

    /**
     * Verify the user's PIN for authentication purposes.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->user();
        $profile = $user->userProfile;

        if ($profile === null || ! $profile->pin) {
            return static::errorResponse('PIN not set', 422);
        }

        if (! Hash::check($validatedData['pin'], $profile->pin)) {
            return static::errorResponse('Invalid PIN', 401);
        }

        return static::successResponse(message: 'PIN verified');
    }

    /**
     * Reset the user's PIN without requiring the current PIN (e.g., for forgotten PIN).
     */
    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->user();
        $profile = $user->userProfile()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'preferred_language' => 'en',
        ]);

        $profile->update(['pin' => Hash::make($validatedData['pin'])]);

        return static::successResponse(message: 'PIN reset successfully');
    }
}
