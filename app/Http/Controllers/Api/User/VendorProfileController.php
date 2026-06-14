<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\VendorInventoryResource;
use App\Http\Resources\Vendor\VendorProfileResource;
use App\Models\ProductStatus;
use App\Models\User;
use App\Models\UserType;
use App\Models\VendorInventoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    /**
     * Relationships loaded for the vendor.
     */
    private const VENDOR_RELATIONS = ['vendorProfile'];

    /**
     * Relationships loaded for the vendor's products.
     */
    private const PRODUCT_RELATIONS = [
        'product.productCategory',
        'product.type',
        'product.defaultUnit',
        'product.status',
        'packagingType',
        'unit',
        'currency',
        'vendor.vendorProfile',
        'status',
        'ratings',
    ];

    /**
     * Display the specified vendor profile and their active products.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $vendor = User::where('user_type_id', UserType::VENDOR_ID)
            ->with(self::VENDOR_RELATIONS)
            ->withCount([
                'vendorInventories as active_inventories_count' => static fn (Builder $query): Builder => $query->where('inventory_status_id', VendorInventoryStatus::AVAILABLE_ID),
            ])
            ->find((int) $id);

        if (! $vendor) {
            abort(404, __('api.general.not_found'));
        }

        $products = $vendor->vendorInventories()
            ->where('inventory_status_id', VendorInventoryStatus::AVAILABLE_ID)
            ->whereHas(
                'product',
                static fn (Builder $query): Builder => $query->where(
                    'product_status_id', ProductStatus::PUBLISHED_ID
                )
            )
            ->with(self::PRODUCT_RELATIONS)
            ->paginate($request->integer('per_page', 10));

        $paginatedProducts = VendorInventoryResource::collection($products)->response()->getData(true);

        return static::successResponse([
            'vendor' => new VendorProfileResource($vendor),
            'products' => $paginatedProducts,
        ], __('api.product.retrieved'));
    }
}
