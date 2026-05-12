<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Models\UserStatus;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Show authenticated user's profile information.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return static::successResponse(
            new UserResource($user->loadMissing('userProfile')),
            __('api.profile.retrieved')
        );
    }

    /**
     * Update authenticated user's profile information.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->handleProfileUpdate($request, false);
    }

    /**
     * Replace authenticated user's profile information.
     */
    public function replace(UpdateProfileRequest $request): JsonResponse
    {
        return $this->handleProfileUpdate($request, true);
    }

    private function handleProfileUpdate(
        UpdateProfileRequest $request,
        bool $isReplace
    ): JsonResponse {
        $user = $this->authenticatedUser($request);

        $updatedUser = $this->profileService->updateProfile(
            $user,
            $request->validated(),
            $request->file('image')
        );

        $key = $isReplace ? 'profile.replaced' : 'profile.updated';

        return static::successResponse(
            new UserResource($updatedUser),
            __("api.{$key}")
        );
    }

    /**
     * Delete the authenticated user account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $user->update([
            'user_status_id' => UserStatus::DELETED_ID,
            'deleted_at' => Carbon::now(),
        ]);

        return static::successResponse(message: __('api.profile.deleted'));
    }
}
