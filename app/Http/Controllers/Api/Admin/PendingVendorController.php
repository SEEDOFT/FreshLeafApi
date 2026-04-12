<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePendingVendorRequest;
use App\Http\Resources\Admin\PendingVendorResource;
use App\Models\Vendor;
use App\Models\VendorStatus;
use Illuminate\Http\JsonResponse;

class PendingVendorController extends Controller
{
    public function index(): JsonResponse
    {
        $vendors = Vendor::query()
            ->where('status_id', VendorStatus::PENDING)
            ->orderByDesc('id')
            ->paginate(15);

        return $this->successResponse([
            'items' => PendingVendorResource::collection($vendors->items()),
            'pagination' => [
                'current_page' => $vendors->currentPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
                'last_page' => $vendors->lastPage(),
            ],
        ], 'Pending vendors loaded');
    }

    public function show(Vendor $vendor): JsonResponse
    {
        if ((int) $vendor->status_id !== VendorStatus::PENDING) {
            abort(404, 'Pending vendor not found.');
        }

        return $this->successResponse(new PendingVendorResource($vendor), 'Pending vendor loaded');
    }

    public function update(UpdatePendingVendorRequest $request, Vendor $vendor): JsonResponse
    {
        if ((int) $vendor->status_id !== VendorStatus::PENDING) {
            return $this->errorResponse('Vendor is not pending approval.', 422);
        }

        $isApproveAction = $request->string('action')->toString() === 'approve';

        $vendor->update([
            'status_id' => $isApproveAction ? VendorStatus::ACTIVE : VendorStatus::INACTIVE,
            'is_verified' => $isApproveAction,
        ]);

        return $this->successResponse(new PendingVendorResource($vendor->fresh()), $isApproveAction ? 'Vendor approved successfully' : 'Vendor rejected successfully');
    }
}
