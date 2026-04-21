<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePendingVendorRequest;
use App\Http\Resources\Admin\PendingVendorResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the pending vendor approval requests.
     */
    public function indexPendingVendorApproval(Request $request): JsonResponse
    {
        /** @var User|null $admin */
        $admin = $request->user();
        if (! $admin) {
            return static::errorResponse('Unauthenticated', 401);
        }

        if (! (bool) $admin->adminProfile?->super_admin) {
            return static::errorResponse('Forbidden', 403);
        }

        $pendingVendors = User::with(['vendorProfile', 'status', 'type'])
            ->where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::PENDING)
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            PendingVendorResource::collection($pendingVendors),
            'Pending vendor approval requests retrieved successfully.'
        );
    }

    /**
     * Show a specific pending vendor approval request.
     */
    public function showPendingVendorApproval(Request $request, int $id): JsonResponse
    {
        /** @var User|null $admin */
        $admin = $request->user();
        if (! $admin) {
            return static::errorResponse('Unauthenticated', 401);
        }

        if (! (bool) $admin->adminProfile?->super_admin) {
            return static::errorResponse('Forbidden', 403);
        }

        $vendor = User::with(['vendorProfile', 'status', 'type'])
            ->where('id', $id)
            ->where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::PENDING)
            ->first();

        if (! $vendor) {
            return static::errorResponse('Vendor request not found', 404);
        }

        return static::successResponse(
            new PendingVendorResource($vendor),
            'Pending vendor approval request retrieved successfully.'
        );
    }

    /**
     * Update a pending vendor approval request.
     */
    public function updatePendingVendorApproval(int $id, UpdatePendingVendorRequest $request): JsonResponse
    {
        /** @var User|null $admin */
        $admin = $request->user();
        if (! $admin) {
            return static::errorResponse('Unauthenticated', 401);
        }

        if (! (bool) $admin->adminProfile?->super_admin) {
            return static::errorResponse('Forbidden', 403);
        }

        $vendor = User::with(['vendorProfile', 'status', 'type'])
            ->where('id', $id)
            ->where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::PENDING)
            ->first();

        if (! $vendor) {
            return static::errorResponse('Vendor request not found', 404);
        }

        $validatedData = $request->validated();
        $isApproved = $validatedData['action'] === 'approve';
        $reason = $validatedData['reason'] ?? null;
        $adminId = (int) $admin->id;

        $vendor->update([
            'user_status_id' => $isApproved ? UserStatus::ACTIVE : UserStatus::INACTIVE,
        ]);

        $vendorProfile = $vendor->vendorProfile()->firstOrCreate(['user_id' => $vendor->id]);
        $vendorProfile->update([
            'is_verified' => $isApproved,
            'approved_at' => $isApproved ? \now() : null,
            'approved_by_admin_id' => $isApproved ? $adminId : null,
            'approve_reason' => $isApproved ? $reason : null,
            'rejected_at' => $isApproved ? null : \now(),
            'rejected_by_admin_id' => $isApproved ? null : $adminId,
            'reject_reason' => $isApproved ? null : $reason,
        ]);

        return static::successResponse(
            new PendingVendorResource($vendor->fresh()->load(['vendorProfile', 'status', 'type'])),
            $isApproved ? 'Vendor approved successfully.' : 'Vendor rejected successfully.'
        );
    }
}
