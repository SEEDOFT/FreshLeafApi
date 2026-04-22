<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PaymentMethod\ReplacePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\User\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the user's payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('viewAny', PaymentMethod::class);

        $paymentMethods = $user->paymentMethods()
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($request->integer('per_page', 10));

        return $this->successResponse(
            PaymentMethodResource::collection($paymentMethods),
            message: 'Payment methods retrieved successfully'
        );
    }

    /**
     * Display the specified payment method.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }

        Gate::authorize('view', $paymentMethod);

        return $this->successResponse(new PaymentMethodResource($paymentMethod));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        Gate::authorize('create', PaymentMethod::class);

        $data = \array_merge(
            $request->validated(),
            ['payment_method_status_id' => PaymentMethodStatus::ACTIVE]
        );

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $paymentMethod = $user->paymentMethods()->create([
            'user_id' => $user->id,
            'payment_method_status_id' => $data['payment_method_status_id'],
            'label' => $data['label'],
            'payment_method_type_id' => $data['payment_method_type_id'],
            'card_holder_name' => $data['card_holder_name'],
            'card_number' => $data['card_number'],
            'expiry_month' => $data['expiry_month'],
            'expiry_year' => $data['expiry_year'],
            'cvv' => $data['cvv'],
            'is_default' => $data['is_default'] ?? false,
            'billing_address' => $data['billing_address'],
            'billing_city' => $data['billing_city'],
            'billing_state' => $data['billing_state'],
            'billing_zip_code' => $data['billing_zip_code'],
        ]);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            'Payment method created successfully',
            201
        );
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }

        Gate::authorize('update', $paymentMethod);

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
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
    public function replace(ReplacePaymentMethodRequest $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }

        Gate::authorize('update', $paymentMethod);

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
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
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }

        Gate::authorize('delete', $paymentMethod);

        $paymentMethod->update([
            'payment_method_status_id' => PaymentMethodStatus::DELETE,
            'is_default' => false,
            'deleted_at' => \now(),
        ]);

        return $this->successResponse(message: 'Payment method deleted successfully');
    }
}
