<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->adminIndex($request);
    }

    /**
     * Display products for user mode (available for sale only).
     */
    public function userIndex(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
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
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
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
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse($products, 'Vendor products loaded successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return static::successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status'])),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        return static::successResponse(
            new ProductResource($product->load([
                'category', 'type', 'defaultUnit', 'status', 'variants', 'vendor',
            ])),
            'Product loaded successfully'
        );
    }

    /**
     * Display a product for admin mode (all products).
     */
    public function adminShow(string $id): JsonResponse
    {
        return $this->show($id);
    }

    /**
     * Display a product for user mode (must be available for sale).
     */
    public function userShow(string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        if ((int) $product->product_status_id !== ProductStatus::ACTIVE) {
            \abort(404, 'Product not found.');
        }

        return static::successResponse(
            new ProductResource($product->load([
                'category', 'type', 'defaultUnit', 'status', 'variants', 'vendor',
            ])),
            'Product loaded successfully'
        );
    }

    /**
     * Display a product for vendor mode (must belong to current vendor).
     */
    public function vendorShow(Request $request, string $id): JsonResponse
    {
        /** @var User|null $vendor */
        $vendor = $request->user();
        if (! $vendor) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        $vendorId = (int) $vendor->getAuthIdentifier();

        if ((int) $product->vendor_user_id !== $vendorId) {
            \abort(404, 'Product not found.');
        }

        return static::successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status', 'variants', 'vendor'])),
            'Product loaded successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        $product->update($request->validated());

        return static::successResponse(
            new ProductResource($product->fresh()->load(['category', 'type', 'defaultUnit', 'status', 'variants'])),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        $product->delete();

        return static::successResponse(message: 'Product deleted successfully');
    }
}
