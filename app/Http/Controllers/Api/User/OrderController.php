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
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::with(['status', 'paymentStatus', 'type'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, int $id): JsonResponse|OrderResource
    {
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
            ->where('user_id', auth()->id())
            ->find($id);

        if (! $order) {
            return response()->json([
                'status' => [
                    'code' => '404',
                    'success' => false,
                    'message' => trans('api.general.not_found', ['model' => 'Order']),
                ],
                'data' => null,
            ], 404);
        }

        return new OrderResource($order);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', auth()->id())->find($id);

        if (! $order) {
            return response()->json([
                'status' => [
                    'code' => '404',
                    'success' => false,
                    'message' => trans('api.general.not_found', ['model' => 'Order']),
                ],
                'data' => null,
            ], 404);
        }

        if ($order->order_status_id !== OrderStatus::PENDING_ID) {
            return response()->json([
                'status' => [
                    'code' => '422',
                    'success' => false,
                    'message' => __('api.order.cannot_cancel'),
                ],
                'data' => null,
            ], 422);
        }

        $order->order_status_id = OrderStatus::CANCELLED_ID;
        $order->save();

        return response()->json([
            'status' => [
                'code' => '200',
                'success' => true,
                'message' => __('api.order.cancelled'),
            ],
            'data' => new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
        ]);
    }

    /**
     * Confirm receipt of an order.
     */
    public function confirmReceipt(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', auth()->id())->find($id);

        if (! $order) {
            return response()->json([
                'status' => [
                    'code' => '404',
                    'success' => false,
                    'message' => trans('api.general.not_found', ['model' => 'Order']),
                ],
                'data' => null,
            ], 404);
        }

        if (! in_array($order->order_status_id, [OrderStatus::DELIVERED_ID, OrderStatus::PREPARING_ID], true)) {
            return response()->json([
                'status' => [
                    'code' => '422',
                    'success' => false,
                    'message' => __('api.order.cannot_confirm_receipt'),
                ],
                'data' => null,
            ], 422);
        }

        $order->order_status_id = OrderStatus::DELIVERED_ID;
        $order->save();

        return response()->json([
            'status' => [
                'code' => '200',
                'success' => true,
                'message' => __('api.order.receipt_confirmed'),
            ],
            'data' => new OrderResource($order->fresh(['status', 'paymentStatus', 'type'])),
        ]);
    }
}
