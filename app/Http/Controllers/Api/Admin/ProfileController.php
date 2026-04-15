<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Show Admin Profile Info
     */
    public function show(): JsonResponse
    {
        return static::successResponse(
            new AdminProfileResource($this->user()->loadMissing('adminProfile')),
            'Admin profile loaded'
        );
    }

    /**
     * Partial Update Admin Profile
     */
    public function update(UpdateAdminProfileRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $admin = $this->user();

        $profile = $admin->adminProfile()->firstOrCreate(['user_id' => $admin->id]);
        $profile->update($validatedData);

        return static::successResponse(
            new AdminProfileResource($admin->fresh()->load('adminProfile')),
            'Admin profile updated'
        );
    }
}
