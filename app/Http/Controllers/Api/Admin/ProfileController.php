<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    /**
     * Show Admin Profile Info
     */
    public function show(Request $request): JsonResponse
    {
        $admin = $this->authenticatedUser($request);

        Gate::authorize('view', [$admin, UserType::ADMIN]);

        return static::successResponse(
            new AdminProfileResource($admin->loadMissing('adminProfile')),
            'Admin profile loaded'
        );
    }

    /**
     * Partial Update Admin Profile
     */
    public function update(UpdateAdminProfileRequest $request): JsonResponse
    {
        $admin = $this->authenticatedUser($request);

        Gate::authorize('update', [$admin, UserType::ADMIN]);

        $validatedData = $request->validated();

        $profile = $admin->adminProfile()->firstOrCreate(['user_id' => $admin->id]);

        $profile->update($validatedData);

        return static::successResponse(
            new AdminProfileResource($admin->fresh()->load('adminProfile')),
            'Admin profile updated'
        );
    }
}
