<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\VendorInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of active products (vendor inventories) for sale.
     */
    public function index(Request $request): JsonResponse
    {
        $listings = VendorInventory::active()
            ->whereHas('product', fn ($query) => $query->active())
            ->with([
                'product.productCategory',
                'product.type',
                'product.defaultUnit',
                'product.status',
                'unit',
                'vendor',
                'status',
            ])
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->whereHas('product', fn ($q) => $q->where('product_category_id', $request->input('category_id')));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('product', fn ($q) => $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_km', 'like', "%{$search}%")
                );
            })
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successTrans(
            VendorInventoryResource::collection($listings),
            'product.products_retrieved'
        );
    }

    /**
     * Display the specified product listing.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $listing = VendorInventory::active()
            ->whereHas('product', fn ($query) => $query->active())
            ->find($id);

        if (! $listing) {
            return static::notFoundTranslated('product.not_found');
        }

        return static::successTrans(
            new VendorInventoryResource($listing->load([
                'product.productCategory',
                'product.type',
                'product.defaultUnit',
                'product.status',
                'unit',
                'vendor',
                'status',
            ])),
            'product.retrieved'
        );
    }
}
