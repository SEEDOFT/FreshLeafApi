<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CapturePayPalOrderRequest;
use App\Http\Requests\Payment\CreatePaymentIntentRequest;
use App\Http\Requests\Payment\CreatePayPalOrderRequest;
use App\Http\Requests\Payment\ProcessRefundRequest;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\PaymentType;
use App\Services\PayPalService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected PayPalService $payPalService
    ) {}

    /**
     * Create a Stripe payment intent.
     */
    public function createPaymentIntent(CreatePaymentIntentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = Auth::user();

            $amountInCents = (int) \round($data['amount'] * 100);

            $paymentIntentData = [
                'amount' => $amountInCents,
                'currency' => $data['currency'] ?? 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'user_id' => $user->id,
                    'order_id' => $data['order_id'] ?? '',
                ],
            ];

            $paymentIntent = $this->stripeService->createPaymentIntent($paymentIntentData);

            return $this->successResponse([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $data['amount'],
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
            ], 'Payment intent created successfully');
        } catch (Throwable $e) {
            Log::error('Payment intent creation failed: '.$e->getMessage());

            return $this->errorResponse('Failed to create payment intent', 500);
        }
    }

    /**
     * Confirm a Stripe payment.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => ['required', 'string'],
        ]);

        try {
            $paymentIntent = $this->stripeService->getPaymentIntent($request->payment_intent_id);

            if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
                $this->recordPayment($paymentIntent);

                return $this->successResponse([
                    'payment_intent_id' => $paymentIntent->id,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                ], 'Payment confirmed successfully');
            }

            return $this->errorResponse('Payment not yet completed. Current status: '.$paymentIntent->status, 400);
        } catch (Throwable $e) {
            Log::error('Payment confirmation failed: '.$e->getMessage());

            return $this->errorResponse('Failed to confirm payment', 500);
        }
    }

    /**
     * Process a Stripe refund.
     */
    public function refund(ProcessRefundRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $paymentIntent = $this->stripeService->getPaymentIntent($data['payment_intent_id']);

            if ($paymentIntent->status !== PaymentIntent::STATUS_SUCCEEDED) {
                return $this->errorResponse('Can only refund succeeded payments', 400);
            }

            $refundAmount = isset($data['amount'])
                ? (int) \round($data['amount'] * 100)
                : null;

            $refund = $this->stripeService->createRefund(
                $data['payment_intent_id'],
                $refundAmount
            );

            return $this->successResponse([
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'currency' => $refund->currency,
                'status' => $refund->status,
            ], 'Refund processed successfully');
        } catch (Throwable $e) {
            Log::error('Refund failed: '.$e->getMessage());

            return $this->errorResponse('Failed to process refund', 500);
        }
    }

    /**
     * Handle Stripe webhooks.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = \config('services.stripe.webhook_secret');

        try {
            $event = $this->stripeService->verifyWebhookSignature($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            return $this->errorResponse('Invalid webhook signature', 400);
        }

        try {
            DB::transaction(function () use ($event) {
                match ($event->type) {
                    'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
                    'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
                    'payment_intent.canceled' => $this->handlePaymentCanceled($event->data->object),
                    'charge.refunded' => $this->handleChargeRefunded($event->data->object),
                    default => Log::info('Unhandled Stripe event: '.$event->type),
                };
            });

            return $this->successResponse(message: 'Webhook handled successfully');
        } catch (Throwable $e) {
            Log::error('Webhook handling failed: '.$e->getMessage());

            return $this->errorResponse('Webhook handling failed', 500);
        }
    }

    /**
     * Get Stripe payment status.
     */
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => ['required', 'string'],
        ]);

        try {
            $paymentIntent = $this->stripeService->getPaymentIntent($request->payment_intent_id);

            return $this->successResponse([
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'created' => $paymentIntent->created,
            ]);
        } catch (Throwable $e) {
            Log::error('Payment status check failed: '.$e->getMessage());

            return $this->errorResponse('Failed to retrieve payment status', 500);
        }
    }

    /**
     * Create a PayPal order.
     */
    public function createPayPalOrder(CreatePayPalOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = Auth::user();

            $orderData = [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'reference_id' => 'order_'.$data['order_id'] ?? 'default',
                'custom_id' => (string) $user->id,
                'description' => $data['description'] ?? 'Payment for order',
                'return_url' => $data['return_url'],
                'cancel_url' => $data['cancel_url'],
            ];

            $order = $this->payPalService->createOrder($orderData);

            return $this->successResponse([
                'order_id' => $order['id'],
                'status' => $order['status'],
                'approve_url' => $order['approve_url'],
                'links' => $order['links'],
            ], 'PayPal order created successfully');
        } catch (Throwable $e) {
            Log::error('PayPal order creation failed: '.$e->getMessage());

            return $this->errorResponse('Failed to create PayPal order', 500);
        }
    }

    /**
     * Capture a PayPal order (complete payment).
     */
    public function capturePayPalOrder(CapturePayPalOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $order = $this->payPalService->captureOrder($data['order_id']);

            $this->recordPayPalPayment($order);

            return $this->successResponse([
                'order_id' => $order['id'],
                'status' => $order['status'],
                'purchase_units' => $order['purchase_units'] ?? [],
            ], 'PayPal payment captured successfully');
        } catch (Throwable $e) {
            Log::error('PayPal capture failed: '.$e->getMessage());

            return $this->errorResponse('Failed to capture PayPal payment', 500);
        }
    }

    /**
     * Get PayPal order details.
     */
    public function getPayPalOrderStatus(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        try {
            $order = $this->payPalService->getOrder($request->order_id);

            return $this->successResponse([
                'order_id' => $order['id'],
                'status' => $order['status'],
                'intent' => $order['intent'],
                'purchase_units' => $order['purchase_units'],
                'payment_source' => $order['payment_source'],
                'create_time' => $order['create_time'],
                'update_time' => $order['update_time'],
            ]);
        } catch (Throwable $e) {
            Log::error('PayPal order status check failed: '.$e->getMessage());

            return $this->errorResponse('Failed to retrieve PayPal order status', 500);
        }
    }

    /**
     * Process a PayPal refund.
     */
    public function refundPayPal(ProcessRefundRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $refund = $this->payPalService->refundCapture(
                $data['payment_intent_id'],
                $data['amount'] ?? null,
                $data['currency'] ?? 'USD'
            );

            return $this->successResponse([
                'refund_id' => $refund['id'],
                'amount' => $refund['amount'],
                'currency' => $refund['currency'],
                'status' => $refund['status'],
            ], 'PayPal refund processed successfully');
        } catch (Throwable $e) {
            Log::error('PayPal refund failed: '.$e->getMessage());

            return $this->errorResponse('Failed to process PayPal refund', 500);
        }
    }

    /**
     * Handle PayPal webhooks.
     */
    public function handlePayPalWebhook(Request $request): JsonResponse
    {
        $data = $request->all();

        try {
            $isValid = $this->payPalService->verifyWebhook($data);

            if (! $isValid) {
                return $this->errorResponse('Invalid webhook signature', 400);
            }

            $eventType = $data['event_type'] ?? null;
            $resource = $data['resource'] ?? [];

            if (! $eventType) {
                return $this->errorResponse('Missing event_type', 400);
            }

            DB::transaction(function () use ($eventType, $resource) {
                match ($eventType) {
                    'PAYMENT.CAPTURE.COMPLETED' => $this->handlePayPalCaptureCompleted($resource),
                    'PAYMENT.CAPTURE.DENIED' => $this->handlePayPalCaptureDenied($resource),
                    'PAYMENT.CAPTURE.REFUNDED' => $this->handlePayPalCaptureRefunded($resource),
                    'CHECKOUT.ORDER.APPROVED' => $this->handlePayPalOrderApproved($resource),
                    default => Log::info('Unhandled PayPal webhook event: '.$eventType),
                };
            });

            return $this->successResponse(message: 'PayPal webhook handled successfully');
        } catch (Throwable $e) {
            Log::error('PayPal webhook handling failed: '.$e->getMessage());

            return $this->errorResponse('PayPal webhook handling failed', 500);
        }
    }

    /**
     * Record a successful Stripe payment in the database.
     */
    protected function recordPayment(PaymentIntent $paymentIntent): ?Payment
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        $userId = $paymentIntent->metadata->user_id ?? null;

        if (! $orderId || ! $userId) {
            return null;
        }

        return Payment::updateOrCreate(
            [
                'transaction_reference' => $paymentIntent->id,
            ],
            [
                'order_id' => $orderId,
                'payment_type_id' => PaymentType::CARD,
                'payment_status_id' => PaymentStatus::COMPLETED,
                'amount' => $paymentIntent->amount / 100,
                'paid_at' => \now(),
            ]
        );
    }

    /**
     * Record a successful PayPal payment in the database.
     */
    protected function recordPayPalPayment(array $order): ?Payment
    {
        $purchaseUnits = $order['purchase_units'] ?? [];

        if (empty($purchaseUnits)) {
            return null;
        }

        $purchaseUnit = $purchaseUnits[0];
        $customId = $purchaseUnit['customId'] ?? null;
        $invoiceId = $purchaseUnit['invoiceId'] ?? null;

        $captures = $purchaseUnit['payments']['captures'] ?? [];

        if (empty($captures)) {
            return null;
        }

        $capture = $captures[0];
        $amount = $capture['amount']['value'] ?? null;

        return Payment::updateOrCreate(
            [
                'transaction_reference' => $capture['id'] ?? $order['id'],
            ],
            [
                'order_id' => $invoiceId,
                'payment_type_id' => PaymentType::CARD,
                'payment_status_id' => PaymentStatus::COMPLETED,
                'amount' => $amount,
                'paid_at' => \now(),
            ]
        );
    }

    /**
     * Handle Stripe payment_intent.succeeded event.
     */
    protected function handlePaymentSucceeded(PaymentIntent $paymentIntent): void
    {
        $this->recordPayment($paymentIntent);

        Log::info('Stripe payment succeeded: '.$paymentIntent->id);
    }

    /**
     * Handle Stripe payment_intent.payment_failed event.
     */
    protected function handlePaymentFailed(PaymentIntent $paymentIntent): void
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            Payment::updateOrCreate(
                [
                    'transaction_reference' => $paymentIntent->id,
                ],
                [
                    'order_id' => $orderId,
                    'payment_type_id' => PaymentType::CARD,
                    'payment_status_id' => PaymentStatus::FAILED,
                    'amount' => $paymentIntent->amount / 100,
                ]
            );
        }

        Log::error('Stripe payment failed: '.$paymentIntent->id);
    }

    /**
     * Handle Stripe payment_intent.canceled event.
     */
    protected function handlePaymentCanceled(PaymentIntent $paymentIntent): void
    {
        Log::info('Stripe payment canceled: '.$paymentIntent->id);
    }

    /**
     * Handle Stripe charge.refunded event.
     */
    protected function handleChargeRefunded($charge): void
    {
        $paymentIntentId = $charge->payment_intent;

        if ($paymentIntentId) {
            Payment::where('transaction_reference', $paymentIntentId)
                ->update(['payment_status_id' => PaymentStatus::REFUNDED]);
        }

        Log::info('Stripe charge refunded: '.$charge->id);
    }

    /**
     * Handle PayPal PAYMENT.CAPTURE.COMPLETED event.
     */
    protected function handlePayPalCaptureCompleted(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        $amount = $resource['amount']['value'] ?? null;
        $captureId = $resource['id'] ?? null;

        Log::info('PayPal capture completed: '.$captureId);
    }

    /**
     * Handle PayPal PAYMENT.CAPTURE.DENIED event.
     */
    protected function handlePayPalCaptureDenied(array $resource): void
    {
        $captureId = $resource['id'] ?? null;

        Log::error('PayPal capture denied: '.$captureId);
    }

    /**
     * Handle PayPal PAYMENT.CAPTURE.REFUNDED event.
     */
    protected function handlePayPalCaptureRefunded(array $resource): void
    {
        $captureId = $resource['id'] ?? null;

        if ($captureId) {
            Payment::where('transaction_reference', $captureId)
                ->update(['payment_status_id' => PaymentStatus::REFUNDED]);
        }

        Log::info('PayPal capture refunded: '.$captureId);
    }

    /**
     * Handle PayPal CHECKOUT.ORDER.APPROVED event.
     */
    protected function handlePayPalOrderApproved(array $resource): void
    {
        $orderId = $resource['id'] ?? null;

        Log::info('PayPal order approved: '.$orderId);
    }
}
