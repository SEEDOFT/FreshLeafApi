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
        $product = Product::find($id);

        if (! $product) {
            return static::errorResponse('Product not found', 404);
        }

        return static::successResponse(
            new ProductResource($product->load([
                'productCategory',
                'type',
                'defaultUnit',
                'status',
            ])),
            'Product loaded successfully'
        );
    }
}
