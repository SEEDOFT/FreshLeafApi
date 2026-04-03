<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ReplaceUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }
            $data['image'] = $this->storeUserImage($request->file('image'));
        }

        $user->update($data);

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    public function replace(ReplaceUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('image')) {

            if ($user->image) {
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }
            $data['image'] = $this->storeUserImage($request->file('image'));
        }

        $user->update($data);

        return $this->successResponse(new UserResource($user), 'User replaced successfully');
    }

    public function destroy(): JsonResponse
    {
        $user = auth()->user();
        $user->update(['user_status_id' => UserStatus::DELETED]);

        return $this->successResponse(message: 'User deleted successfully');
    }

    private function storeUserImage($file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('users', $fileName, 'public');

        return $fileName;
    }
}
