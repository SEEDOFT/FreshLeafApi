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
        return static::successResponse(
            new UserResource(
                $this->authenticatedUser($request)
                    ->loadMissing('userProfile')
            ),
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

    /**
     * Handle both update and replace profile logic.
     */
    private function handleProfileUpdate(
        UpdateProfileRequest $request,
        bool $isReplace
    ): JsonResponse {

        $updatedUser = $this->profileService->updateProfile(
            $this->authenticatedUser($request),
            $request->validated(),
            $request->file('image')
        );

        $message = $isReplace ? __('api.profile.replaced') : __('api.profile.updated');

        return static::successResponse(
            new UserResource($updatedUser),
            $message
        );
    }

    /**
     * Delete the authenticated user account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authenticatedUser($request)
            ->update([
                'user_status_id' => UserStatus::DELETED_ID,
                'deleted_at' => Carbon::now(),
            ]);

        return static::successResponse(message: __('api.profile.deleted'));
    }
}
