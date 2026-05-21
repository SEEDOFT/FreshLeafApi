<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PaymentMethod\ReplacePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\User\PaymentMethodResource;
use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use function array_merge;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the user's payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $paymentMethods = $user->paymentMethods()
            ->active()
            ->isType(PaymentMethodType::CREDIT_DEBIT_ID)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            PaymentMethodResource::collection($paymentMethods),
            __('api.payment_method.payment_methods_retrieved')
        );
    }

    /**
     * Display the specified payment method.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $paymentMethod = $user->paymentMethods()
            ->active()
            ->isType(PaymentMethodType::CREDIT_DEBIT_ID)
            ->find($id);

        if (! $paymentMethod) {
            return static::notFoundResponse(__('api.payment_method.not_found'));
        }

        return static::successResponse(
            new PaymentMethodResource($paymentMethod),
            __('api.payment_method.retrieved')
        );
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $data = array_merge(
            $request->validated(),
            ['payment_method_status_id' => PaymentMethodStatus::ACTIVE_ID]
        );

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()
                ->isType(PaymentMethodType::CREDIT_DEBIT_ID)
                ->update(['is_default' => false]);
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
            __('api.payment_method.created'),
            201
        );
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(string $id, UpdatePaymentMethodRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->notFoundResponse(__('api.payment_method.not_found'));
        }

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($data);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            __('api.payment_method.updated')
        );
    }

    /**
     * Replace the specified payment method in storage.
     */
    public function replace(string $id, ReplacePaymentMethodRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $paymentMethod = $user->paymentMethods()->active()->find($id);

        if (! $paymentMethod) {
            return $this->notFoundResponse(__('api.payment_method.not_found'));
        }

        $validatedData = $request->validated();

        if ($validatedData['is_default'] ?? false) {
            $user->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($validatedData);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            __('api.payment_method.replaced')
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
            return $this->notFoundResponse(__('api.payment_method.not_found'));
        }

        $paymentMethod->update([
            'payment_method_status_id' => PaymentMethodStatus::DELETE_ID,
            'is_default' => false,
            'deleted_at' => Carbon::now(),
        ]);

        return $this->successResponse(message: __('api.payment_method.deleted'));
    }
}
