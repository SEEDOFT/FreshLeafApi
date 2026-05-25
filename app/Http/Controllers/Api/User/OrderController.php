<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

        return static::successResponse(
            OrderResource::collection($orders),
            __('api.order.orders_retrieved')
        );
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
            return static::notFoundResponse(trans('api.general.not_found', ['model' => 'Order']));
        }

        return static::successResponse(
            new OrderResource($order),
            __('api.order.retrieved')
        );
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find($id);

        if (! $order) {
            return static::notFoundResponse(trans('api.general.not_found', ['model' => 'Order']));
        }

        if ($order->order_status_id !== OrderStatus::PENDING_ID) {
            return static::errorResponse(__('api.order.cannot_cancel'), 422);
        }

        $order->order_status_id = OrderStatus::CANCELLED_ID;
        $order->save();

        return static::successResponse(
            new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
            __('api.order.cancelled')
        );
    }

    /**
     * Confirm receipt of an order.
     */
    public function confirmReceipt(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find($id);

        if (! $order) {
            return static::notFoundResponse(trans('api.general.not_found', ['model' => 'Order']));
        }

        if (! in_array($order->order_status_id, [OrderStatus::DELIVERED_ID, OrderStatus::PREPARING_ID], true)) {
            return static::errorResponse(__('api.order.cannot_confirm_receipt'), 422);
        }

        $order->order_status_id = OrderStatus::DELIVERED_ID;
        $order->save();

        return static::successResponse(
            new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
            __('api.order.receipt_confirmed')
        );
    }
}
