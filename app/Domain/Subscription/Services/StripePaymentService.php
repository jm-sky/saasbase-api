<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\DTOs\PaymentDetailsDTO;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;

class StripePaymentService extends StripeService
{
    /**
     * Attach a client-side-tokenized payment method (created via Stripe.js/Elements)
     * to a customer and set it as the default. Raw card data is never accepted or
     * handled here — only the resulting Stripe `pm_...` id.
     *
     * @throws StripeException
     */
    public function attachPaymentMethod(BillingCustomer $billingCustomer, PaymentDetailsDTO $paymentDetails): string
    {
        return $this->handleStripeException(function () use ($billingCustomer, $paymentDetails) {
            $paymentMethodId = $paymentDetails->paymentMethodId;

            // Attach payment method to customer
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $billingCustomer->stripe_customer_id,
            ]);

            // Set as default payment method
            $this->stripe->customers->update($billingCustomer->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            return $paymentMethodId;
        });
    }
}
