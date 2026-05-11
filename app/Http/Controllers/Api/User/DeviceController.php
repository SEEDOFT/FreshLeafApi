<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreDeviceRequest;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;

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
            ['device_token' => $validatedData['device_token']],
            [
                'user_id' => $user->id,
                'device_type' => $validatedData['device_type'] ?? null,
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
    public function destroy(string $token): JsonResponse
    {
        UserDevice::where('device_token', $token)
            ->update(['is_active' => false]);

        return static::successResponse(message: __('api.device.deactivated'));
    }
}
