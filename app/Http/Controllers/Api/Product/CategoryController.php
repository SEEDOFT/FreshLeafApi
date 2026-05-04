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
        $categories = ProductCategory::where('is_active', true)
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse($categories);
    }

    /**
     * Get a specific category with its products.
     */
    public function show(ProductCategory $category, Request $request): JsonResponse
    {
        if (! $category->is_active) {
            return static::errorResponse('Category not found or inactive', 404);
        }

        if ($request->boolean('include_products')) {
            $category->load(['products' => static function ($query) {
                $query->where('is_active', true);
            }]);
        }

        return static::successResponse($category);
    }
}
