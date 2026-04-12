<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        return $this->successResponse(new AdminProfileResource($admin), 'Admin profile loaded');
    }

    public function update(UpdateAdminProfileRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $validated = $request->safe()->only(['department', 'job_title', 'office_phone']);

        $admin->update($validated);

        return $this->successResponse(new AdminProfileResource($admin->fresh()), 'Admin profile updated');
    }
}
