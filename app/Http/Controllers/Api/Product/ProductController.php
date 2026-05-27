<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\VendorInventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @var list<string>
     */
    private const array RELATIONSHIPS = [
        'product.productCategory',
        'product.type',
        'product.defaultUnit',
        'product.status',
        'packagingType',
        'unit',
        'currency',
        'vendor.vendorProfile',
        'status',
    ];

    /**
     * Display a listing of active products (vendor inventories) for sale.
     */
    public function index(Request $request): JsonResponse
    {
        $listings = VendorInventory::active()
            ->whereHas(
                'product',
                static fn (Builder $query) => $query->where(
                    'product_status_id', ProductStatus::PUBLISHED_ID
                )
            )
            ->with(self::RELATIONSHIPS)
            ->when($request->filled('province'),
                static function (Builder $query) use ($request) {
                    $province = strtolower($request->input('province'));
                    $query->where(static function (Builder $q) use ($province) {
                        $q->whereRaw('lower(province_of_origin) = ?', [$province])
                            ->orWhereHas('vendor.vendorProfile', static function (Builder $vQuery) use ($province) {
                                $vQuery->whereRaw('lower(province) = ?', [$province]);
                            });
                    });
                })
            ->when($request->filled('category_id'),
                static function (Builder $query) use ($request) {
                    $query->whereHas('product',
                        static fn (Builder $product) => $product->where(
                            'product_category_id',
                            (int) $request->input('category_id')
                        )
                    );
                })
            ->when($request->filled('search'),
                static function (Builder $query) use ($request) {
                    $search = strtolower($request->input('search'));
                    $query->whereHas(
                        'product',
                        static function (Builder $product) use ($search) {
                            $product->whereRaw('lower(name_en) like ?', ["%{$search}%"])
                                ->orWhereRaw('lower(name_km) like ?', ["%{$search}%"]);
                        }
                    );
                })
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            ['vendor_inventories' => VendorInventoryResource::collection($listings)],
            __('api.product.retrieved')
        );
    }

    /**
     * Display the specified product listing.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $listing = VendorInventory::active()
            ->whereHas(
                'product',
                static fn (Builder $query) => $query->where(
                    'product_status_id', ProductStatus::PUBLISHED_ID
                )
            )
            ->with(self::RELATIONSHIPS)
            ->find($id);

        if (! $listing) {
            return static::notFoundResponse(__('api.product.not_found'));
        }

        return static::successResponse(
            ['vendor_inventories' => new VendorInventoryResource($listing)],
            __('api.product.retrieved')
        );
    }
}
