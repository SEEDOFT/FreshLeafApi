<?php

namespace App\Services;

use Exception;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\OrderApplicationContextBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentTokenRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\RefundRequestBuilder;
use PaypalServerSdkLib\Models\Builders\VerifyWebhookSignatureRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use Throwable;

class PayPalService
{
    protected $client;

    protected $ordersController;

    protected $paymentsController;

    protected $vaultController;

    protected $webhooksController;

    public function __construct()
    {
        $mode = config('services.paypal.mode', 'sandbox');
        $environment = $mode === 'production' ? Environment::PRODUCTION : Environment::SANDBOX;

        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    config('services.paypal.client_id'),
                    config('services.paypal.secret')
                )
            )
            ->environment($environment)
            ->build();

        $this->ordersController = $this->client->getOrdersController();
        $this->paymentsController = $this->client->getPaymentsController();
        $this->vaultController = $this->client->getVaultController();
        $this->webhooksController = $this->client->getWebhooksController();
    }

    /**
     * Create a PayPal order.
     */
    public function createOrder(array $data): array
    {
        try {
            $amount = AmountWithBreakdownBuilder::init(
                $data['currency'] ?? 'USD',
                number_format($data['amount'], 2, '.', '')
            )->build();

            $purchaseUnit = PurchaseUnitRequestBuilder::init($amount)
                ->referenceId($data['reference_id'] ?? 'default')
                ->customId($data['custom_id'] ?? null)
                ->invoiceId($data['invoice_id'] ?? null)
                ->description($data['description'] ?? null)
                ->build();

            $orderRequest = OrderRequestBuilder::init(
                CheckoutPaymentIntent::CAPTURE,
                [$purchaseUnit]
            );

            if (isset($data['return_url'])) {
                $orderRequest = $orderRequest->applicationContext(
                    OrderApplicationContextBuilder::init()
                        ->returnUrl($data['return_url'])
                        ->cancelUrl($data['cancel_url'] ?? $data['return_url'])
                        ->brandName($data['brand_name'] ?? config('app.name'))
                        ->build()
                );
            }

            $response = $this->ordersController->createOrder([
                'body' => $orderRequest->build(),
                'prefer' => 'return=minimal',
            ]);

            $order = $response->getResult();

            $links = [];
            if (isset($order->links)) {
                foreach ($order->links as $link) {
                    $links[$link->rel] = [
                        'href' => $link->href,
                        'method' => $link->method,
                    ];
                }
            }

            return [
                'id' => $order->id,
                'status' => $order->status,
                'links' => $links,
                'approve_url' => $links['approve']['href'] ?? null,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to create PayPal order: '.$e->getMessage());
        }
    }

    /**
     * Get PayPal order details.
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = $this->ordersController->getOrder([
                'id' => $orderId,
                'fields' => 'payment_source',
            ]);

            $order = $response->getResult();

            return [
                'id' => $order->id,
                'status' => $order->status,
                'intent' => $order->intent,
                'purchase_units' => $order->purchaseUnits ?? [],
                'payment_source' => $order->paymentSource ?? null,
                'create_time' => $order->createTime ?? null,
                'update_time' => $order->updateTime ?? null,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to get PayPal order: '.$e->getMessage());
        }
    }

    /**
     * Capture a PayPal order (complete payment).
     */
    public function captureOrder(string $orderId): array
    {
        try {
            $response = $this->ordersController->captureOrder([
                'id' => $orderId,
                'prefer' => 'return=minimal',
            ]);

            $order = $response->getResult();

            return [
                'id' => $order->id,
                'status' => $order->status,
                'purchase_units' => $order->purchaseUnits ?? [],
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to capture PayPal order: '.$e->getMessage());
        }
    }

    /**
     * Authorize a PayPal order.
     */
    public function authorizeOrder(string $orderId): array
    {
        try {
            $response = $this->ordersController->authorizeOrder([
                'id' => $orderId,
                'prefer' => 'return=minimal',
            ]);

            $order = $response->getResult();

            return [
                'id' => $order->id,
                'status' => $order->status,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to authorize PayPal order: '.$e->getMessage());
        }
    }

    /**
     * Refund a captured PayPal payment.
     */
    public function refundCapture(string $captureId, ?float $amount = null, ?string $currency = 'USD'): array
    {
        try {
            $refundBody = null;

            if ($amount !== null) {
                $refundAmount = MoneyBuilder::init(
                    $currency,
                    number_format($amount, 2, '.', '')
                )->build();

                $refundBody = RefundRequestBuilder::init($refundAmount)->build();
            }

            $response = $this->paymentsController->refundCapturedPayment([
                'captureId' => $captureId,
                'body' => $refundBody,
            ]);

            $refund = $response->getResult();

            return [
                'id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount->value ?? null,
                'currency' => $refund->amount->currencyCode ?? null,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to refund PayPal capture: '.$e->getMessage());
        }
    }

    /**
     * Get refund details.
     */
    public function getRefund(string $refundId): array
    {
        try {
            $response = $this->paymentsController->showRefundDetails([
                'refundId' => $refundId,
            ]);

            $refund = $response->getResult();

            return [
                'id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount->value ?? null,
                'currency' => $refund->amount->currencyCode ?? null,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to get PayPal refund details: '.$e->getMessage());
        }
    }

    /**
     * Create a payment token (vault) for future payments.
     */
    public function createPaymentToken(array $data): array
    {
        try {
            $tokenRequest = PaymentTokenRequestBuilder::init(
                $data['payment_source']
            )->build();

            $response = $this->vaultController->createPaymentToken([
                'body' => $tokenRequest,
            ]);

            $result = $response->getResult();

            return [
                'id' => $result->id,
                'customer' => $result->customer ?? null,
                'payment_source' => $result->paymentSource ?? null,
            ];
        } catch (Throwable $e) {
            throw new Exception('Failed to create PayPal payment token: '.$e->getMessage());
        }
    }

    /**
     * Verify webhook event.
     */
    public function verifyWebhook(array $data): bool
    {
        try {
            $webhookId = config('services.paypal.webhook_id');

            $response = $this->webhooksController->verifyWebhookSignature([
                'body' => VerifyWebhookSignatureRequestBuilder::init(
                    $data['auth_algo'] ?? '',
                    $data['cert_url'] ?? '',
                    $data['transmission_id'] ?? '',
                    $data['transmission_sig'] ?? '',
                    $data['transmission_time'] ?? '',
                    $data['webhook_id'] ?? $webhookId,
                    $data['webhook_event'] ?? []
                )->build(),
            ]);

            $result = $response->getResult();

            return $result->verificationStatus === 'SUCCESS';
        } catch (Throwable $e) {
            return false;
        }
    }
}
