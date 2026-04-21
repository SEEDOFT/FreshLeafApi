<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PaymentMethod\ReplacePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\User\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\User\PaymentMethodResource;
use App\Models\PaymentMethodStatus;
use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserPaymentMethodController extends Controller
{
    /**
     * Display a listing of the user's payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        Gate::authorize('viewAny', UserPaymentMethod::class);

        $paymentMethods = $user->paymentMethods()
            ->where('payment_method_status_id', PaymentMethodStatus::ACTIVE)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($request->integer('per_page', 10));

        return $this->successResponse(
            PaymentMethodResource::collection($paymentMethods),
            message: 'Payment methods retrieved successfully'
        );
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        Gate::authorize('create', UserPaymentMethod::class);

        $data = \array_merge(
            $request->validated(),
            ['payment_method_status_id' => PaymentMethodStatus::ACTIVE]
        );

        if ($data['is_default'] ?? false) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $paymentMethod = $user->paymentMethods()->create($data);

        return $this->successResponse(
            new PaymentMethodResource($paymentMethod),
            'Payment method created successfully',
            201
        );
    }

    /**
     * Display the specified payment method.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $paymentMethod = UserPaymentMethod::query()->find($id);
        if (! $paymentMethod) {
            return $this->errorResponse('Payment method not found', 404);
        }

        Gate::authorize('view', $paymentMethod);

        return $this->successResponse(new PaymentMethodResource($paymentMethod));
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $paymentMethod = UserPaymentMethod::query()->find($id);
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
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $paymentMethod = UserPaymentMethod::query()->find($id);
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
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $paymentMethod = UserPaymentMethod::query()->find($id);
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
