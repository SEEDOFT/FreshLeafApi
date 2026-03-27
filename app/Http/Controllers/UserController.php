<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display the authenticated user's information.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(new UserResource($user));
    }

    /**
     * Update the authenticated user's information.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($user->id !== Auth::id()) {
            return $this->errorResponse('User not found', 404);
        }

        $user->fill($request->validated());
        $user->save();

        $user->refresh();

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    /**
     * Soft delete the authenticated user's account.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id !== Auth::id()) {
            return $this->errorResponse('User not found', 404);
        }

        $user->delete();

        return $this->successResponse(message: 'User deleted successfully');
    }
}
