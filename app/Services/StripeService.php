<?php

namespace App\Services;

use Stripe\Customer;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe customer.
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Retrieve a Stripe customer.
     */
    public function getCustomer(string $customerId): Customer
    {
        return Customer::retrieve($customerId);
    }

    /**
     * Attach a payment method to a customer.
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): StripePaymentMethod
    {
        $paymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);

        return $paymentMethod;
    }

    /**
     * Detach a payment method from a customer.
     */
    public function detachPaymentMethod(string $paymentMethodId): StripePaymentMethod
    {
        $paymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();

        return $paymentMethod;
    }

    /**
     * List payment methods for a customer.
     */
    public function listPaymentMethods(string $customerId, string $type = 'card'): array
    {
        return StripePaymentMethod::all([
            'customer' => $customerId,
            'type' => $type,
        ])->data;
    }

    /**
     * Create a payment intent.
     */
    public function createPaymentIntent(array $data): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'usd',
            'customer' => $data['customer_id'] ?? null,
            'payment_method' => $data['payment_method_id'] ?? null,
            'confirm' => $data['confirm'] ?? false,
            'automatic_payment_methods' => $data['automatic_payment_methods'] ?? null,
            'metadata' => $data['metadata'] ?? [],
            'return_url' => $data['return_url'] ?? null,
        ]);
    }

    /**
     * Retrieve a payment intent.
     */
    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Confirm a payment intent.
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $data = []): PaymentIntent
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->confirm($data);

        return $paymentIntent;
    }

    /**
     * Cancel a payment intent.
     */
    public function cancelPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->cancel();

        return $paymentIntent;
    }

    /**
     * Create a refund.
     */
    public function createRefund(string $paymentIntentId, ?int $amount = null): Refund
    {
        $refundData = ['payment_intent' => $paymentIntentId];

        if ($amount !== null) {
            $refundData['amount'] = $amount;
        }

        return Refund::create($refundData);
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $sigHeader, string $webhookSecret): Event
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
    }

    /**
     * Set default payment method for a customer.
     */
    public function setDefaultPaymentMethod(string $customerId, string $paymentMethodId): Customer
    {
        return Customer::update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
    }
}
