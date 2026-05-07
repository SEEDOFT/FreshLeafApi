<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PaymentMethodTypeResource;
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
            __('api.payment_method_type.payment_method_types_retrieved')
        );
    }

    /**
     * Display the specified payment method type for users.
     */
    public function show(string $id): JsonResponse
    {
        $type = PaymentMethodType::find($id);
        if (! $type) {
            return static::notFoundResponse(__('api.payment_method_type.not_found'));
        }

        return static::successResponse(
            new PaymentMethodTypeResource($type),
            __('api.payment_method_type.retrieved')
        );
    }
}
