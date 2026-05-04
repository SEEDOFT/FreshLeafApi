<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\UpdateProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        return match ($user->user_type_id) {
            UserType::ADMIN => static::successResponse(
                new AdminProfileResource($user->loadMissing('adminProfile')),
                'Admin profile loaded'
            ),
            UserType::VENDOR => static::successResponse(
                new VendorProfileResource($user->loadMissing('vendorProfile')),
                'Vendor profile loaded'
            ),
            UserType::USER => static::successResponse(
                new UserResource($user->loadMissing('userProfile')),
                'User profile loaded'
            ),
            default => static::errorResponse('Unauthorized user type', 403),
        };
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

    private function handleProfileUpdate(UpdateProfileRequest $request, bool $isReplace): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        Gate::authorize('update', [$user, $user->user_type_id]);

        $updatedUser = $this->profileService->updateProfile(
            $user,
            $request->validated(),
            $request->file('image')
        );

        return match ($updatedUser->user_type_id) {
            UserType::ADMIN => static::successResponse(new AdminProfileResource($updatedUser), 'Admin profile updated'),
            UserType::VENDOR => static::successResponse(new VendorProfileResource($updatedUser), 'Vendor profile updated'),
            UserType::USER => static::successResponse(new UserResource($updatedUser), $isReplace ? 'User replaced successfully' : 'User updated successfully'),
            default => static::errorResponse('Unauthorized', 403),
        };
    }

    /**
     * Delete the authenticated user account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('delete', [$user, $user->user_type_id]);

        $user->update([
            'user_status_id' => UserStatus::DELETED,
            'deleted_at' => now(),
        ]);

        return static::successResponse(message: 'User deleted successfully');
    }
}
