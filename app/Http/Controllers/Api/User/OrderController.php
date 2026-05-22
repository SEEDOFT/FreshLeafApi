<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
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
            ->where('user_id', $request->user()->id)
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
            'address',
        ])
            ->where('user_id', $request->user()->id)
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
}
