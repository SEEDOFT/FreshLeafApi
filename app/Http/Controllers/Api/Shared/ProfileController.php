<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\UpdateProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
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
        $user = $this->authenticatedUser($request);

        return $this->persistProfile($user, $request->validated(), $request, false);
    }

    /**
     * Replace authenticated user's profile information.
     */
    public function replace(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return $this->persistProfile($user, $request->validated(), $request, true);
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

    /**
     * Persist profile data.
     *
     * @param  array<string, mixed>  $validatedData
     */
    private function persistProfile(
        User $user,
        array $validatedData,
        Request $request,
        bool $isReplace
    ): JsonResponse {
        Gate::authorize('update', [$user, $user->user_type_id]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }
            $validatedData['image'] = $this->storeUserImage($request->file('image'));
        }

        // Update User model (common fields)
        $user->update(\array_intersect_key($validatedData, \array_flip([
            'first_name', 'last_name', 'email', 'phone_number', 'password', 'image',
        ])));

        // Update specific profile
        match ($user->user_type_id) {
            UserType::ADMIN => $user->adminProfile()->firstOrCreate(['user_id' => $user->id])->update($validatedData),
            UserType::VENDOR => $user->vendorProfile()->firstOrCreate(['user_id' => $user->id])->update($validatedData),
            UserType::USER => $user->userProfile()->firstOrCreate(['user_id' => $user->id])->update($validatedData),
            default => null,
        };

        return match ($user->user_type_id) {
            UserType::ADMIN => static::successResponse(new AdminProfileResource($user->fresh()->load('adminProfile')), 'Admin profile updated'),
            UserType::VENDOR => static::successResponse(new VendorProfileResource($user->fresh()->load('vendorProfile')), 'Vendor profile updated'),
            UserType::USER => static::successResponse(new UserResource($user->fresh()->load('userProfile')), $isReplace ? 'User replaced successfully' : 'User updated successfully'),
            default => static::errorResponse('Unauthorized', 403),
        };
    }

    /**
     * Store user image and return the file name.
     */
    private function storeUserImage(UploadedFile $file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('users', $fileName, 'public');

        return $fileName;
    }
}
