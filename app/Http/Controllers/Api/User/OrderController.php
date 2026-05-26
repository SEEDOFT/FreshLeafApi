<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\OrderPayRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\Wallet;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $orders = Order::with(['status', 'paymentStatus', 'type'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        try {
            return static::successResponse(
                OrderResource::collection($orders),
                __('api.orders.retrieved')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::with([
            'status',
            'paymentStatus',
            'type',
            'items.vendorInventory.product',
            'items.vendorInventory.unit',
            'items.vendorInventory.currency',
            'items.vendorInventory.activeDiscount',
            'address',
        ])
            ->where('user_id', $user->id)
            ->find($id);

        if (! $order) {
            return static::notFoundResponse(__('api.general.not_found', ['model' => 'Order']));
        }

        try {
            return static::successResponse(
                new OrderResource($order),
                __('api.order.retrieved')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find($id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        if ($order->order_status_id !== OrderStatus::PENDING_ID) {
            abort(422, __('api.order.cannot_cancel'));
        }

        $order->update([
            'order_status_id' => OrderStatus::CANCELLED_ID,
        ]);

        try {
            return static::successResponse(
                new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
                __('api.order.cancelled')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Confirm receipt of an order.
     */
    public function confirmReceipt(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find($id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        if (! in_array($order->order_status_id, [OrderStatus::DELIVERED_ID, OrderStatus::PREPARING_ID], true)) {
            abort(422, __('api.order.cannot_confirm_receipt'));
        }

        $order->update([
            'order_status_id' => OrderStatus::DELIVERED_ID,
        ]);

        try {
            return static::successResponse(
                new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
                __('api.order.receipt_confirmed')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Pay for an order using Wallet.
     */
    public function pay(OrderPayRequest $request, int $id, OrderService $orderService): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::with(['payments'])->where('user_id', $user->id)->find($id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        $validated = $request->validated();
        $wallet = Wallet::where('id', $validated['wallet_id'])->firstOrFail();

        $orderService->payWithWallet($user, $order, $wallet);

        return static::successResponse(
            new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
            __('api.order.payment_successful', ['default' => 'Payment successful'])
        );
    }

    /**
     * Simulate an external payment completion (e.g. ABA/Acleda/Credit Card).
     */
    public function simulateExternalPayment(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::with(['payments'])->where('user_id', $user->id)->find($id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        if ($order->order_status_id !== OrderStatus::AWAITING_PAYMENT_ID) {
            abort(422, 'Order is not awaiting payment.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status_id' => OrderStatus::PENDING_ID,
                'payment_status_id' => PaymentStatus::COMPLETED_ID,
            ]);

            $order->histories()->create([
                'order_status_id' => OrderStatus::PENDING_ID,
                'notes' => 'External payment completed successfully.',
            ]);

            foreach ($order->payments as $payment) {
                if ($payment->status_id === PaymentStatus::PENDING_ID) {
                    $payment->update(['status_id' => PaymentStatus::COMPLETED_ID]);
                    $payment->histories()->create([
                        'payment_status_id' => PaymentStatus::COMPLETED_ID,
                        'notes' => 'External payment simulated as completed.',
                    ]);
                }
            }
        });

        return static::successResponse(
            new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
            __('api.order.payment_successful', ['default' => 'Payment successful'])
        );
    }
}
