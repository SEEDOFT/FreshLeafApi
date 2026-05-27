<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\VendorInventoryResource;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\ProductStatus;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    /**
     * Display the specified vendor profile and their active products.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $vendor = User::where('user_type_id', UserType::VENDOR_ID)
            ->with(['vendorProfile'])
            ->withCount([
                'vendorInventories as active_inventories_count' => static fn (User $query): Builder => $query->active(),
            ])
            ->find((int) $id);

        if (! $vendor) {
            abort(404, __('api.general.not_found'));
        }

        $products = $vendor->vendorInventories()
            ->active()
            ->whereHas(
                'product',
                static fn (Builder $query): Builder => $query->where(
                    'product_status_id', ProductStatus::PUBLISHED_ID
                )
            )
            ->with([
                'product.productCategory',
                'product.type',
                'product.defaultUnit',
                'product.status',
                'packagingType',
                'unit',
                'currency',
                'vendor.vendorProfile',
                'status',
            ])
            ->get();

        return static::successResponse([
            'vendor' => new VendorProfileResource($vendor),
            'products' => VendorInventoryResource::collection($products),
        ], __('api.product.retrieved'));
    }
}
