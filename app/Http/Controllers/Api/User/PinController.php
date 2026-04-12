<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Pin\SetPinRequest;
use App\Http\Requests\User\Pin\UpdatePinRequest;
use App\Http\Requests\User\Pin\VerifyPinRequest;
use App\Models\ConsumerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = ConsumerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['preferred_language' => 'en']
        );

        if ($profile->pin) {
            return $this->errorResponse('PIN already set', 422);
        }

        $profile->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN set successfully');
    }

    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = ConsumerProfile::query()->where('user_id', $user->id)->first();

        if (! $profile || ! $profile->pin) {
            return $this->errorResponse('PIN not set', 422);
        }

        if (! Hash::check($request->current_pin, $profile->pin)) {
            return $this->errorResponse('Invalid current PIN', 401);
        }

        $profile->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN updated successfully');
    }

    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = ConsumerProfile::query()->where('user_id', $user->id)->first();

        if (! $profile || ! $profile->pin) {
            return $this->errorResponse('PIN not set', 422);
        }

        if (! Hash::check($request->pin, $profile->pin)) {
            return $this->errorResponse('Invalid PIN', 401);
        }

        return $this->successResponse(message: 'PIN verified');
    }

    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = ConsumerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['preferred_language' => 'en']
        );

        $profile->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN reset successfully');
    }
}
