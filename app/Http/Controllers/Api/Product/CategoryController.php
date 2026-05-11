<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all active product categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = ProductCategory::active()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse($categories, __('api.category.categories_retrieved'));
    }

    /**
     * Get a specific category with its products.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $category = ProductCategory::where('id', $id)->active()->first();

        if (! $category) {
            return static::notFoundResponse(__('api.category.not_found'));
        }

        return static::successResponse($category, __('api.category.retrieved'));
    }
}
