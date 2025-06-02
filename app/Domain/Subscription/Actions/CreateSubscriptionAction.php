<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Events\SubscriptionCreated;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action to create a new subscription (Stripe + local).
 */
class CreateSubscriptionAction
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService
    ) {
    }

    /**
     * Create a new subscription for a billable entity.
     *
     * @param array{
     *     billing_customer_id: string,
     *     plan_id: string,
     *     trial_end?: string,
     *     payment_behavior?: string,
     *     metadata?: array<string, mixed>
     * } $data
     *
     * @throws StripeException
     */
    public function __invoke(array $data): string
    {
        try {
            return DB::transaction(function () use ($data) {
                // Find required models
                $billingCustomer = BillingCustomer::findOrFail($data['billing_customer_id']);
                $plan            = SubscriptionPlan::findOrFail($data['plan_id']);

                // Create subscription in Stripe and locally
                $subscription = $this->stripeSubscriptionService->createSubscription(
                    $billingCustomer,
                    $plan,
                    [
                        'trial_end'        => $data['trial_end'] ?? null,
                        'payment_behavior' => $data['payment_behavior'] ?? null,
                        'metadata'         => $data['metadata'] ?? [],
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
