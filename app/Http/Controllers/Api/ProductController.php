<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductStatus;
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
        $products = Product::query()
            ->active()
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return $this->paginateResponse($products, 'Products available for sale loaded successfully');
    }

    /**
     * Display products for admin mode (all products).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return $this->paginateResponse($products, 'All products loaded successfully');
    }

    /**
     * Display products for vendor mode (owned products only).
     */
    public function vendorIndex(Request $request): JsonResponse
    {
        $vendorId = (int) $request->user()->getAuthIdentifier();

        $products = Product::query()
            ->byVendor($vendorId)
            ->with(['category', 'type', 'defaultUnit', 'status', 'vendor'])
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return $this->paginateResponse($products, 'Vendor products loaded successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return $this->successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status'])),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status', 'variants', 'vendor'])),
            'Product loaded successfully'
        );
    }

    /**
     * Display a product for admin mode (all products).
     */
    public function adminShow(Product $product): JsonResponse
    {
        return $this->show($product);
    }

    /**
     * Display a product for user mode (must be available for sale).
     */
    public function userShow(Product $product): JsonResponse
    {
        if ((int) $product->product_status_id !== ProductStatus::ACTIVE) {
            abort(404, 'Product not found.');
        }

        return $this->successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status', 'variants', 'vendor'])),
            'Product loaded successfully'
        );
    }

    /**
     * Display a product for vendor mode (must belong to current vendor).
     */
    public function vendorShow(Request $request, Product $product): JsonResponse
    {
        $vendorId = (int) $request->user()->getAuthIdentifier();

        if ((int) $product->vendor_user_id !== $vendorId) {
            abort(404, 'Product not found.');
        }

        return $this->successResponse(
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status', 'variants', 'vendor'])),
            'Product loaded successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return $this->successResponse(
            new ProductResource($product->fresh()->load(['category', 'type', 'defaultUnit', 'status', 'variants'])),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->successResponse(message: 'Product deleted successfully');
    }

    /**
     * Build a standard paginated product response.
     */
    private function paginateResponse($products, string $message): JsonResponse
    {
        return $this->successResponse([
            'items' => ProductResource::collection($products->items()),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'last_page' => $products->lastPage(),
        ], $message);
    }
}
