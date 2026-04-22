<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\PaymentMethodTypeResource;
use App\Models\PaymentMethodType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodTypeController extends Controller
{
    /**
     * Display a listing of payment method types for users.
     */
    public function index(Request $request): JsonResponse
    {
        $types = PaymentMethodType::orderBy('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            PaymentMethodTypeResource::collection($types),
            'Payment method types retrieved successfully'
        );
    }

    /**
     * Display the specified payment method type for users.
     */
    public function show(string $id): JsonResponse
    {
        $type = PaymentMethodType::find($id);
        if (! $type) {
            return static::errorResponse('Payment method type not found', 404);
        }

        return static::successResponse(
            new PaymentMethodTypeResource($type),
            'Payment method type retrieved successfully'
        );
    }
}
