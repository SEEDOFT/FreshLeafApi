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
        $user = $this->authenticatedUser($request);

        $device = UserDevice::updateOrCreate(
            ['device_token' => $request->string('device_token')->toString()],
            [
                'user_id' => $user->id,
                'device_type' => $request->string('device_type')->toString() ?: null,
                'is_active' => true,
            ]
        );

        return $this->successResponse(
            [
                'device_token' => $device->device_token,
                'device_type' => $device->device_type,
            ],
            'Device registered successfully'
        );
    }

    /**
     * Deactivate a user device token (on logout).
     */
    public function destroy(string $token): JsonResponse
    {
        UserDevice::where('device_token', $token)->update(['is_active' => false]);

        return $this->successResponse([], 'Device deactivated successfully');
    }
}
