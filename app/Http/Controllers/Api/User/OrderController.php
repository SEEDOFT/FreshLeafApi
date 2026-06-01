<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\OrderBatchPayRequest;
use App\Http\Requests\Api\Order\OrderPayRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\Wallet;
use App\Services\InvoicePdfService;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function abort;
use function count;
use function in_array;

class OrderController extends Controller
{
    /**
     * Relationships loaded for the index view and actions returning updated orders.
     */
    private const INDEX_RELATIONS = ['status', 'paymentStatus', 'type'];

    /**
     * Relationships loaded for the detailed order view.
     */
    private const SHOW_RELATIONS = [
        'status',
        'paymentStatus',
        'type',
        'items.vendorInventory.product',
        'items.vendorInventory.unit',
        'items.vendorInventory.currency',
        'items.vendorInventory.activeDiscount',
        'address',
    ];

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $orders = Order::with(self::INDEX_RELATIONS)
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        try {
            return static::successResponse(
                OrderResource::collection($orders),
                __('api.order.orders_retrieved')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Display the specified order.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::with(self::SHOW_RELATIONS)
            ->where('user_id', $user->id)
            ->find((int) $id);

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
    public function cancel(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find((int) $id);

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
                new OrderResource($order->fresh(self::INDEX_RELATIONS)),
                __('api.order.cancelled')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Confirm receipt of an order.
     */
    public function confirmReceipt(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find((int) $id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        if (
            ! in_array(
                $order->order_status_id,
                [OrderStatus::DELIVERED_ID, OrderStatus::PREPARING_ID],
                true
            )
        ) {
            abort(422, __('api.order.cannot_confirm_receipt'));
        }

        $order->update([
            'order_status_id' => OrderStatus::DELIVERED_ID,
        ]);

        try {
            return static::successResponse(
                new OrderResource($order->fresh(self::INDEX_RELATIONS)),
                __('api.order.receipt_confirmed')
            );
        } catch (RuntimeException) {
            abort(422, __('api.general.error'));
        }
    }

    /**
     * Pay for an order using Wallet.
     */
    public function pay(string $id, OrderPayRequest $request, OrderService $orderService): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);
        $order = Order::with(['payments'])
            ->where('user_id', $user->id)
            ->find((int) $id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }
        $wallet = $user->wallets()
            ->where('id', $validatedData['wallet_id'])
            ->first();

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        $orderService->payWithWallet($user, $order, $wallet);

        return static::successResponse(
            new OrderResource($order->fresh(self::INDEX_RELATIONS)),
            __('api.order.payment_successful', ['default' => 'Payment successful'])
        );
    }

    /**
     * Pay for multiple orders using Wallet.
     */
    public function batchPay(OrderBatchPayRequest $request, OrderService $orderService): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        /** @var Collection<int, Order> $orders */
        $orders = Order::with(['payments'])
            ->where('user_id', $user->id)
            ->whereIn('id', $validatedData['order_ids'])
            ->get();

        if ($orders->count() !== count($validatedData['order_ids'])) {
            abort(404, __('api.general.not_found'));
        }

        $wallet = $user->wallets()
            ->where('id', $validatedData['wallet_id'])
            ->first();

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        $orderService->batchPayWithWallet($user, $orders, $wallet);

        return static::successResponse(
            OrderResource::collection($orders->fresh(self::INDEX_RELATIONS)),
            __('api.order.payment_successful', ['default' => 'Payment successful'])
        );
    }

    /**
     * Simulate an external payment completion (e.g. ABA/Acleda/Credit Card).
     */
    public function simulateExternalPayment(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::with(['payments'])
            ->where('user_id', $user->id)
            ->find((int) $id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        if ($order->order_status_id !== OrderStatus::AWAITING_PAYMENT_ID) {
            abort(422, __('api.order.not_awaiting_payment'));
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
            new OrderResource($order->fresh(self::INDEX_RELATIONS)),
            __('api.order.payment_successful', ['default' => 'Payment successful'])
        );
    }

    /**
     * Get a signed URL to download the invoice.
     */
    public function getInvoiceUrl(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $order = Order::where('user_id', $user->id)->find($id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        $url = URL::temporarySignedRoute(
            'v1.orders.invoice.download',
            now()->addMinutes(30),
            ['id' => $id]
        );

        return static::successResponse(
            ['url' => $url],
            __('api.general.success')
        );
    }

    /**
     * Stream the invoice PDF.
     */
    public function downloadInvoice(string $id, Request $request): StreamedResponse
    {
        $order = Order::find((int) $id);

        if (! $order) {
            abort(404, __('api.general.not_found'));
        }

        $pdfContent = InvoicePdfService::generate($order);

        if ($request->boolean('inline')) {
            return response()->stream(
                fn () => print $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="invoice-'.$order->order_number.'.pdf"',
                ]
            );
        }

        return response()->streamDownload(
            fn () => print $pdfContent,
            "invoice-{$order->order_number}.pdf"
        );
    }
}
