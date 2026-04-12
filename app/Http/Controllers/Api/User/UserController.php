<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ReplaceUserRequest;
use App\Http\Requests\User\UpdateConsumerProfileRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\ConsumerProfile;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(new UserResource($user));
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }

            $validatedData['image'] = $this->storeUserImage($request->file('image'));
        }

        $user->update($validatedData);

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    public function replace(ReplaceUserRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }

            $validatedData['image'] = $this->storeUserImage($request->file('image'));
        }

        $user->update($validatedData);

        return $this->successResponse(new UserResource($user), 'User replaced successfully');
    }

    public function destroy(): JsonResponse
    {
        $user = Auth::user();

        $user->update([
            'user_status_id' => UserStatus::DELETED,
            'deleted_at' => now(),
        ]);

        return $this->successResponse(message: 'User deleted successfully');
    }

    public function consumerProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = ConsumerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'date_of_birth' => null,
                'gender' => null,
                'preferred_language' => 'en',
                'preferences' => null,
            ]
        );

        return $this->successResponse($this->consumerProfilePayload($profile), 'Consumer profile loaded');
    }

    public function updateConsumerProfile(UpdateConsumerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $profile = ConsumerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'preferred_language' => $validated['preferred_language'] ?? 'en',
                'preferences' => $validated['preferences'] ?? null,
            ]
        );

        return $this->successResponse($this->consumerProfilePayload($profile), 'Consumer profile updated');
    }

    private function consumerProfilePayload(ConsumerProfile $profile): array
    {
        return [
            'date_of_birth' => optional($profile->date_of_birth)->toDateString(),
            'gender' => $profile->gender,
            'preferred_language' => $profile->preferred_language,
            'preferences' => $profile->preferences,
            'updated_at' => optional($profile->updated_at)->toIso8601String(),
        ];
    }

    private function storeUserImage($file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('users', $fileName, 'public');

        return $fileName;
    }
}
