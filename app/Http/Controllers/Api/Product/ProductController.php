<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products based on user type.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return match ((int) $user?->user_type_id) {
            UserType::ADMIN => $this->adminIndex($request),
            UserType::VENDOR => $this->vendorIndex($request),
            default => $this->userIndex($request),
        };
    }

    /**
     * Display the specified product based on user type.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return match ((int) $user?->user_type_id) {
            UserType::ADMIN => $this->adminShow($id),
            UserType::VENDOR => $this->vendorShow($id, $request),
            default => $this->userShow($id),
        };
    }

    /**
     * Display products for user mode (available for sale only).
     */
    public function userIndex(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with(['productCategory', 'type', 'defaultUnit', 'status', 'vendor'])
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

    /**
     * Display the specified product for admin.
     */
    public function adminShow(int $id): JsonResponse
    {
        $product = Product::query()->find($id);
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

    /**
     * Display the specified product for vendor.
     */
    public function vendorShow(int $id, Request $request): JsonResponse
    {
        /** @var User|null $vendor */
        $vendor = $request->user();
        if (! $vendor) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $vendorId = (int) $vendor->id;

        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        if ((int) $product->user_id !== $vendorId) {
            \abort(404, 'Product not found.');
        }

        return static::successResponse(
            new ProductResource($product->load(['productCategory', 'type', 'defaultUnit', 'status', 'variants', 'vendor'])),
            'Product loaded successfully'
        );
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return static::successResponse(
            new ProductResource($product->load(['productCategory', 'organicCategory', 'type', 'defaultUnit', 'status'])),
            'Product created successfully',
            201
        );
    }

    /**
     * Update the specified product in storage.
     */
    public function update(int $id, UpdateProductRequest $request): JsonResponse
    {
        /** @var User|null $vendor */
        $vendor = $request->user();
        if (! $vendor) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $vendorId = (int) $vendor->id;

        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        if ((int) $product->user_id !== $vendorId) {
            \abort(404, 'Product not found.');
        }

        $product->update($request->validated());

        return static::successResponse(
            new ProductResource($product->fresh()->load(['productCategory', 'type', 'defaultUnit', 'status', 'variants'])),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        /** @var User|null $vendor */
        $vendor = $request->user();
        if (! $vendor) {
            return static::errorResponse('Unauthenticated', 401);
        }

        $vendorId = (int) $vendor->id;

        $product = Product::query()->find($id);
        if (! $product) {
            \abort(404, 'Product not found.');
        }

        if ((int) $product->user_id !== $vendorId) {
            \abort(404, 'Product not found.');
        }

        $product->delete();

        return static::successResponse(message: 'Product deleted successfully');
    }
}
