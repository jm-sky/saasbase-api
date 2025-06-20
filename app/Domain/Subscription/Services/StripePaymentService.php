<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\DTOs\PaymentDetailsDTO;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;

class StripePaymentService extends StripeService
{
    /**
     * Create a payment method for a customer.
     *
     * @throws StripeException
     */
    public function createPaymentMethod(BillingCustomer $billingCustomer, PaymentDetailsDTO $paymentDetails): string
    {
        return $this->handleStripeException(function () use ($billingCustomer, $paymentDetails) {
            // Create payment method in Stripe
            $paymentMethod = $this->stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number'    => $paymentDetails->cardNumber,
                    'exp_month' => (int) explode('/', $paymentDetails->expiry)[0],
                    'exp_year'  => (int) '20' . explode('/', $paymentDetails->expiry)[1],
                    'cvc'       => $paymentDetails->cvc,
                ],
                'billing_details' => [
                    'name' => $paymentDetails->name,
                ],
            ]);

            // Attach payment method to customer
            $this->stripe->paymentMethods->attach($paymentMethod->id, [
                'customer' => $billingCustomer->stripe_customer_id,
            ]);

            // Set as default payment method
            $this->stripe->customers->update($billingCustomer->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->id,
                ],
            ]);

            return $paymentMethod->id;
        });
    }
}
