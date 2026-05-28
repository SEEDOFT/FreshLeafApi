<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreDeviceRequest;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Store or update a user device token for FCM.
     */
    public function store(StoreDeviceRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_token_hash' => hash('sha256', $validatedData['device_token']),
            ],
            [
                'device_token' => $validatedData['device_token'],
                'device_type' => $validatedData['device_type'],
                'is_active' => true,
            ]
        );

        return static::successResponse([
            'device_token' => $device->device_token,
            'device_type' => $device->device_type,
        ], __('api.device.registered'));
    }

    /**
     * Deactivate a user device token (on logout).
     */
    public function destroy(Request $request, string $token): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        UserDevice::where('user_id', $user->id)
            ->where('device_token_hash', hash('sha256', $token))
            ->update(['is_active' => false]);

        return static::successResponse(message: __('api.device.deactivated'));
    }
}
