<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pin\SetPinRequest;
use App\Http\Requests\Pin\UpdatePinRequest;
use App\Http\Requests\Pin\VerifyPinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->pin) {
            return $this->errorResponse('PIN already set', 422);
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN set successfully');
    }

    public function updatePin(UpdatePinRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (! Hash::check($request->current_pin, $user->pin)) {
            return $this->errorResponse('Invalid current PIN', 401);
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN updated successfully');
    }

    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->pin) {
            return $this->errorResponse('PIN not set', 422);
        }

        if (! Hash::check($request->pin, $user->pin)) {
            return $this->errorResponse('Invalid PIN', 401);
        }

        return $this->successResponse(message: 'PIN verified');
    }

    public function resetPin(SetPinRequest $request): JsonResponse
    {
        $user = Auth::user();

        $user->update(['pin' => Hash::make($request->pin)]);

        return $this->successResponse(message: 'PIN reset successfully');
    }
}
