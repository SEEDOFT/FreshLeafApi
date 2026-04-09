<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AdminProfile;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = User::query()
            ->where('phone_number', $validatedData['phone_number'])
            ->ofType(UserType::ADMIN)
            ->active()
            ->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login success');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'user_type_id' => $user->user_type_id,
            'user_status_id' => $user->user_status_id,
        ], 'Admin access granted');
    }

    public function overview(): JsonResponse
    {
        return $this->successResponse([
            'users' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('user_status_id', UserStatus::ACTIVE)->count(),
                'consumers' => User::query()->where('user_type_id', UserType::CONSUMER)->count(),
                'vendors' => User::query()->where('user_type_id', UserType::VENDOR)->count(),
                'admins' => User::query()->where('user_type_id', UserType::ADMIN)->count(),
            ],
            'catalog' => [
                'product_categories_total' => ProductCategory::query()->count(),
                'products_total' => Product::query()->count(),
                'product_variants_total' => ProductVariant::query()->count(),
                'suppliers_total' => Supplier::query()->count(),
            ],
        ], 'Admin overview loaded');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = AdminProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'department' => 'Management',
                'job_title' => 'Administrator',
                'office_phone' => $user->phone_number,
                'super_admin' => false,
                'permissions' => null,
            ]
        );

        return $this->successResponse($this->profilePayload($profile), 'Admin profile loaded');
    }

    public function updateProfile(UpdateAdminProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $profile = AdminProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'department' => $validated['department'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'office_phone' => $validated['office_phone'] ?? null,
                'super_admin' => $validated['super_admin'] ?? false,
                'permissions' => $validated['permissions'] ?? null,
            ]
        );

        return $this->successResponse($this->profilePayload($profile), 'Admin profile updated');
    }

    public function pendingVendors(Request $request): JsonResponse
    {
        $this->ensureSuperAdmin($request);

        $vendors = User::query()
            ->with('vendorProfile')
            ->where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::PENDING)
            ->orderByDesc('id')
            ->get()
            ->map(static function (User $vendor): array {
                return [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'last_name' => $vendor->last_name,
                    'email' => $vendor->email,
                    'phone_number' => $vendor->phone_number,
                    'status' => $vendor->user_status_id,
                    'profile' => [
                        'business_name' => $vendor->vendorProfile?->business_name,
                        'contact_phone' => $vendor->vendorProfile?->contact_phone,
                        'city' => $vendor->vendorProfile?->city,
                        'province' => $vendor->vendorProfile?->province,
                        'address' => $vendor->vendorProfile?->address,
                        'is_verified' => $vendor->vendorProfile?->is_verified,
                    ],
                ];
            })
            ->values()
            ->all();

        return $this->successResponse($vendors, 'Pending vendors loaded');
    }

    public function approveVendor(Request $request, User $vendor): JsonResponse
    {
        $this->ensureSuperAdmin($request);

        if (! $vendor->isType(UserType::VENDOR)) {
            return $this->errorResponse('User is not a vendor.', 422);
        }

        if ((int) $vendor->user_status_id !== UserStatus::PENDING) {
            return $this->errorResponse('Vendor is not pending approval.', 422);
        }

        $vendor->update([
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $vendor->vendorProfile()?->update([
            'is_verified' => true,
        ]);

        return $this->successResponse([
            'vendor_id' => $vendor->id,
            'user_status_id' => $vendor->user_status_id,
            'is_verified' => (bool) $vendor->vendorProfile?->is_verified,
        ], 'Vendor approved successfully');
    }

    private function profilePayload(AdminProfile $profile): array
    {
        return [
            'department' => $profile->department,
            'job_title' => $profile->job_title,
            'office_phone' => $profile->office_phone,
            'super_admin' => $profile->super_admin,
            'permissions' => $profile->permissions,
            'updated_at' => optional($profile->updated_at)->toIso8601String(),
        ];
    }

    private function ensureSuperAdmin(Request $request): void
    {
        $admin = $request->user();

        if (! $admin || ! $admin->isType(UserType::ADMIN) || ! (bool) $admin->adminProfile?->super_admin) {
            abort(403, 'Only super admin can perform this action.');
        }
    }
}
