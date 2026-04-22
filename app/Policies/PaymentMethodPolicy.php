<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\PaymentMethodStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentMethodPolicy
{
    /**
     * Determine whether the authenticated user can view payment method list.
     */
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the authenticated user can create a payment method.
     */
    public function create(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the authenticated user can view the payment method.
     */
    public function view(User $user, PaymentMethod $paymentMethod): Response
    {
        return $this->ownsActivePaymentMethod($user, $paymentMethod);
    }

    /**
     * Determine whether the authenticated user can update the payment method.
     */
    public function update(User $user, PaymentMethod $paymentMethod): Response
    {
        return $this->ownsActivePaymentMethod($user, $paymentMethod);
    }

    /**
     * Determine whether the authenticated user can delete the payment method.
     */
    public function delete(User $user, PaymentMethod $paymentMethod): Response
    {
        return $this->ownsActivePaymentMethod($user, $paymentMethod);
    }

    private function ownsActivePaymentMethod(
        User $user,
        PaymentMethod $paymentMethod
    ): Response {
        if (
            (int) $paymentMethod->user_id === (int) $user->id &&
            (int) $paymentMethod->payment_method_status_id ===
                PaymentMethodStatus::ACTIVE
        ) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Payment method not found.');
    }
}
