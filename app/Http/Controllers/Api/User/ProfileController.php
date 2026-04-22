<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\ReplaceUserRequest;
use App\Http\Requests\User\Profile\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Show authenticate user's information
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('view', [$user, UserType::USER]);

        return static::successResponse(new UserResource($user));
    }

    /**
     * Update authenticate user's information
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        return $this->persistUserProfile(
            $request->validated(),
            $request,
            false
        );
    }

    /**
     * Replace entire user's information
     */
    public function replace(ReplaceUserRequest $request): JsonResponse
    {
        return $this->persistUserProfile(
            $request->validated(),
            $request,
            true
        );
    }

    /**
     * Soft delete the authenticate user
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('delete', [$user, UserType::USER]);

        $user->update([
            'user_status_id' => UserStatus::DELETED,
            'deleted_at' => \now(),
        ]);

        return static::successResponse(message: 'User deleted successfully');
    }

    /**
     * Store user image and return the file name
     */
    private function storeUserImage($file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('users', $fileName, 'public');

        return $fileName;
    }

    /**
     * Persist user profile data for both update and replace operations
     */
    private function persistUserProfile(
        array $validatedData,
        UpdateUserRequest|ReplaceUserRequest $request,
        bool $isReplace,
    ): JsonResponse {
        $user = $this->authenticatedUser($request);

        Gate::authorize('update', [$user, UserType::USER]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk(\config('filesystems.default'))
                    ->delete('users/'.$user->image);
            }

            $validatedData['image'] = self::storeUserImage($request->file('image'));
        }

        $user->update($validatedData);

        return static::successResponse(
            new UserResource($user),
            $isReplace ? 'User replaced successfully' : 'User updated successfully'
        );
    }
}
