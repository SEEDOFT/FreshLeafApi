<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Get all active product categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = ProductCategory::active()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            ProductCategoryResource::collection($categories),
            __('api.category.categories_retrieved')
        );
    }

    /**
     * Get a specific category with its products.
     */
    public function show(string $id): JsonResponse
    {
        $category = ProductCategory::active()->find($id);

        if (! $category) {
            return static::notFoundResponse(__('api.category.not_found'));
        }

        return static::successResponse(
            new ProductCategoryResource($category),
            __('api.category.retrieved')
        );
    }
}
