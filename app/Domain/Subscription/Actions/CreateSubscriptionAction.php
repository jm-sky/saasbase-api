<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\DTOs\CreateSubscriptionDTO;
use App\Domain\Subscription\Events\SubscriptionCreated;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Services\StripePaymentService;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action to create a new subscription (Stripe + local).
 */
class CreateSubscriptionAction
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService,
        protected StripePaymentService $stripePaymentService
    ) {
    }

    /**
     * Create a new subscription for a billable entity.
     *
     * @throws StripeException
     */
    public function __invoke(CreateSubscriptionDTO $data): string
    {
        try {
            return DB::transaction(function () use ($data) {
                // Find required models
                $billingInterval = $data->billingInterval;
                $billingCustomer = BillingCustomer::findOrFail($data->billingCustomerId);
                $plan            = SubscriptionPlan::findOrFail($data->planId);

                // Create payment method if provided
                if (isset($data->paymentDetails)) {
                    $this->stripePaymentService->createPaymentMethod($billingCustomer, $data->paymentDetails);
                }

                // Create subscription in Stripe and locally
                $subscription = $this->stripeSubscriptionService->createSubscription(
                    $billingCustomer,
                    $plan,
                    [
                        'trial_end'        => $data->trialEndsAt ?? null,
                        'payment_behavior' => $data->paymentBehavior ?? null,
                        'metadata'         => $data->metadata ?? [],
                        'billing_interval' => $billingInterval,
                    ]
                );

                // Dispatch event
                event(new SubscriptionCreated($subscription));

                return $subscription->stripe_subscription_id;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);

            throw new StripeException(message: 'Failed to create subscription: ' . $e->getMessage(), previous: $e);
        }
    }
}
