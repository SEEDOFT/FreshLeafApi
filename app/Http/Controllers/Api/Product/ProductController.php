<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products based on user type.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with([
                'productCategory',
                'type',
                'defaultUnit',
                'status',
                'vendor',
            ])
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            ProductResource::collection($products),
            'Products available for sale loaded successfully'
        );
    }

    /**
     * Display the specified product based on user type.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $product = Product::find($id);

        if (! $product) {
            return static::errorResponse('Product not found', 404);
        }

        return static::successResponse(
            new ProductResource($product),
            'Product loaded successfully'
        );
    }

    /**
     * Display products for user mode (available for sale only).
     */
    public function userIndex(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with([
                'productCategory',
                'type',
                'defaultUnit',
                'status',
                'vendor',
            ])
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse($products, 'Products available for sale loaded successfully');
    }

    /**
     * Display products for admin mode (all products).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['productCategory', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse($products, 'All products loaded successfully');
    }

    /**
     * Display products for vendor mode (owned products only).
     */
    public function vendorIndex(Request $request): JsonResponse
    {
        /** @var User|null $vendor */
        $vendor = $request->user();
        if (! $vendor) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $vendorId = (int) $vendor->id;

        $products = Product::byVendor($vendorId)
            ->with(['productCategory', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse($products, 'Vendor products loaded successfully');
    }

    /**
     * Display the specified product.
     */
    public function userShow(int $id): JsonResponse
    {
        /** @var Product $product */
        $product = Product::active()->find($id);

        if (! $product) {
            \abort(404, 'Product not found.');
        }

        return static::successResponse(
            new ProductResource($product->load([
                'productCategory', 'organicCategory', 'type', 'defaultUnit', 'status', 'variants', 'vendor',
            ])),
            'Product loaded successfully'
        );
    }
}
