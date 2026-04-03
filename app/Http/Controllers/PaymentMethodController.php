<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMethod\ReplacePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the user's payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $paymentMethods = Auth::user()->paymentMethods()
            ->where('payment_status_id', PaymentStatus::ACTIVE)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($request->integer('per_page', 10));

        return $this->successResponse(PaymentMethodResource::collection($paymentMethods));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $data = array_merge(
            $request->validated(),
            ['payment_status_id' => PaymentStatus::ACTIVE]
        );

        if ($data['is_default'] ?? false) {
            Auth::user()->paymentMethods()->update(['is_default' => false]);
        }

        $paymentMethod = Auth::user()->paymentMethods()->create($data);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            'Payment method created successfully',
            201
        );
    }

    /**
     * Display the specified payment method.
     */
    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== Auth::id() || $paymentMethod->payment_status_id !== PaymentStatus::ACTIVE) {
            return $this->errorResponse('Payment method not found', 404);
        }

        return $this->successResponse(new PaymentMethodResource($paymentMethod));
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== Auth::id() || $paymentMethod->payment_status_id !== PaymentStatus::ACTIVE) {
            return $this->errorResponse('Payment method not found', 404);
        }

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            Auth::user()->paymentMethods()->where('id', '!=', $paymentMethod->id)->update(['is_default' => false]);
        }

        $paymentMethod->update($data);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            'Payment method updated successfully'
        );
    }

    /**
     * Replace the specified payment method in storage.
     */
    public function replace(ReplacePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== Auth::id() || $paymentMethod->payment_status_id !== PaymentStatus::ACTIVE) {
            return $this->errorResponse('Payment method not found', 404);
        }

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            Auth::user()->paymentMethods()->where('id', '!=', $paymentMethod->id)->update(['is_default' => false]);
        }

        $paymentMethod->update($data);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            'Payment method replaced successfully'
        );
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== Auth::id() || $paymentMethod->payment_status_id !== PaymentStatus::ACTIVE) {
            return $this->errorResponse('Payment method not found', 404);
        }

        $paymentMethod->update([
            'payment_status_id' => PaymentStatus::DELETE,
            'is_default' => false,
        ]);

        return $this->successResponse(message: 'Payment method deleted successfully');
    }
}
