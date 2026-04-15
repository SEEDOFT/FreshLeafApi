<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ReplaceUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Show authenticate user's information
     */
    public function show(): JsonResponse
    {
        return static::successResponse(new UserResource($this->user));
    }

    /**
     * Update authenticate user's information
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            if ($this->user->image) {
                Storage::disk(config('filesystems.default'))
                    ->delete("users/$this->user->image");
            }

            $validatedData['image'] = self::storeUserImage($request->file('image'));
        }

        $this->user->update($validatedData);

        return static::successResponse(
            new UserResource($this->user),
            'User updated successfully'
        );
    }

    /**
     * Replace entire user's information
     */
    public function replace(ReplaceUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        if ($request->hasFile('image')) {
            if ($this->user->image) {
                Storage::disk(config('filesystems.default'))
                    ->delete("users/$this->user->image");
            }

            $validatedData['image'] = self::storeUserImage($request->file('image'));
        }

        $this->user->update($validatedData);

        return static::successResponse(
            new UserResource($this->user),
            'User replaced successfully'
        );
    }

    /**
     * Soft delete the authenticate user
     */
    public function destroy(): JsonResponse
    {
        $this->user()->update([
            'user_status_id' => UserStatus::DELETED,
            'deleted_at' => now(),
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
}
