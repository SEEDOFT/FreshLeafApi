<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'type', 'defaultUnit', 'status'])
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse([
            'items' => ProductResource::collection($products->items()),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'last_page' => $products->lastPage(),
        ], 'Products loaded successfully');
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
            new ProductResource($product->load(['category', 'type', 'defaultUnit', 'status', 'variants'])),
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
}
