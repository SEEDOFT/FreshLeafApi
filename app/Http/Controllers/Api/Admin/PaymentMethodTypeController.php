<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplacePaymentMethodTypeRequest;
use App\Http\Requests\Admin\StorePaymentMethodTypeRequest;
use App\Http\Requests\Admin\UpdatePaymentMethodTypeRequest;
use App\Http\Resources\Shared\PaymentMethodTypeResource;
use App\Models\PaymentMethodType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodTypeController extends Controller
{
    /**
     * Display a listing of payment method types.
     */
    public function index(Request $request): JsonResponse
    {
        $types = PaymentMethodType::query()
            ->orderBy('id')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            PaymentMethodTypeResource::collection($types),
            'Payment method types retrieved successfully'
        );
    }

    /**
     * Display the specified payment method type.
     */
    public function show(Request $request, string $id): JsonResponse
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

    /**
     * Store a newly created payment method type.
     */
    public function store(StorePaymentMethodTypeRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $nextId = ((int) PaymentMethodType::max('id')) + 1;

        $type = PaymentMethodType::forceCreate([
            'id' => $nextId,
            'code' => $validatedData['code'],
            'name' => $validatedData['name'],
        ]);

        return static::successResponse(
            new PaymentMethodTypeResource($type),
            'Payment method type created successfully',
            201
        );
    }

    /**
     * Update the specified payment method type.
     */
    public function update(UpdatePaymentMethodTypeRequest $request, string $id): JsonResponse
    {
        $type = PaymentMethodType::find($id);

        if (! $type) {
            return static::errorResponse('Payment method type not found', 404);
        }

        if ($this->isCoreType($type)) {
            return static::errorResponse('Core payment method types cannot be modified', 403);
        }

        $type->update($request->validated());

        return static::successResponse(
            new PaymentMethodTypeResource($type->fresh()),
            'Payment method type updated successfully'
        );
    }

    /**
     * Replace the specified payment method type.
     */
    public function replace(ReplacePaymentMethodTypeRequest $request, string $id): JsonResponse
    {
        $type = PaymentMethodType::find($id);
        if (! $type) {
            return static::errorResponse('Payment method type not found', 404);
        }

        if ($this->isCoreType($type)) {
            return static::errorResponse('Core payment method types cannot be modified', 403);
        }

        $type->update($request->validated());

        return static::successResponse(
            new PaymentMethodTypeResource($type->fresh()),
            'Payment method type replaced successfully'
        );
    }

    /**
     * Remove the specified payment method type.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $type = PaymentMethodType::find($id);
        if (! $type) {
            return static::errorResponse('Payment method type not found', 404);
        }

        if ($this->isCoreType($type)) {
            return static::errorResponse('Core payment method types cannot be deleted', 403);
        }

        $type->delete();

        return static::successResponse(message: 'Payment method type deleted successfully');
    }

    private function isCoreType(PaymentMethodType $type): bool
    {
        return \in_array((int) $type->id, PaymentMethodType::coreTypeIds(), true);
    }
}
