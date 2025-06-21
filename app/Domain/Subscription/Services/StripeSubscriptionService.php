<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Billing\Models\BillingPrice;
use App\Domain\Subscription\DTOs\CheckoutDataDTO;
use App\Domain\Subscription\Enums\BillingInterval;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;

/**
 * Service for managing Stripe subscriptions and local Subscription records.
 */
class StripeSubscriptionService extends StripeService
{
    protected BillingCustomer $billingCustomer;

    /**
     * Create a new subscription for a customer.
     *
     * @param array $options Additional subscription options
     *                       billing_interval: BillingInterval
     *
     * @throws StripeException
     */
    public function createSubscription(
        BillingCustomer $billingCustomer,
        SubscriptionPlan $plan,
        array $options = []
    ): Subscription {
        /** @var BillingPrice $price */
        $price = $plan->getPriceForInterval($options['billing_interval']);

        return $this->handleStripeException(function () use ($billingCustomer, $plan, $price, $options) {
            // Prepare subscription data
            $subscriptionData = [
                'customer' => $billingCustomer->stripe_customer_id,
                'items'    => [
                    [
                        'price' => $price->stripe_price_id,
                    ],
                ],
                'metadata' => [
                    'plan_id' => $plan->id,
                ],
            ];

            // Add trial period if specified
            if (isset($options['trial_end'])) {
                $subscriptionData['trial_end'] = $options['trial_end'];
            }

            // Add payment behavior
            if (isset($options['payment_behavior'])) {
                $subscriptionData['payment_behavior'] = $options['payment_behavior'];
            }

            // Create subscription in Stripe
            $stripeSubscription = $this->stripe->subscriptions->create($subscriptionData);

            // Create local subscription record
            $subscription = new Subscription([
                'stripe_subscription_id' => $stripeSubscription->id,
                'status'                 => $stripeSubscription->status,
                'current_period_start'   => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end'     => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'trial_start'            => $stripeSubscription->trial_start ? Carbon::createFromTimestamp($stripeSubscription->trial_start) : null,
                'trial_end'              => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
            ]);

            $subscription->plan()->associate($plan);
            $billingCustomer->subscriptions()->save($subscription);

            return $subscription;
        });
    }

    /**
     * Update an existing subscription.
     *
     * @param array $options Update options
     *
     * @throws StripeException
     */
    public function updateSubscription(Subscription $subscription, array $options): Subscription
    {
        return $this->handleStripeException(function () use ($subscription, $options) {
            // Prepare update data
            $updateData = [];

            // Handle plan change
            if (isset($options['plan_id'])) {
                $newPlan             = SubscriptionPlan::findOrFail($options['plan_id']);
                $updateData['items'] = [
                    [
                        'id'    => $subscription->stripe_subscription_id,
                        'price' => $newPlan->stripe_price_id,
                    ],
                ];
                $updateData['metadata'] = ['plan_id' => $newPlan->id];
            }

            // Handle proration
            if (isset($options['proration_behavior'])) {
                $updateData['proration_behavior'] = $options['proration_behavior'];
            }

            // Update subscription in Stripe
            $stripeSubscription = $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                $updateData
            );

            // Update local subscription record
            $subscription->update([
                'status'               => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start ? Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                'current_period_end'   => $stripeSubscription->current_period_end ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
            ]);

            if (isset($newPlan)) {
                $subscription->plan()->associate($newPlan);
                $subscription->save();
            }

            return $subscription;
        });
    }

    /**
     * Cancel a subscription.
     *
     * @param bool $cancelAtPeriodEnd Whether to cancel at the end of the current period
     *
     * @throws StripeException
     */
    public function cancelSubscription(Subscription $subscription, bool $cancelAtPeriodEnd = true): Subscription
    {
        return $this->handleStripeException(function () use ($subscription, $cancelAtPeriodEnd) {
            // Cancel subscription in Stripe
            $stripeSubscription = $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => $cancelAtPeriodEnd]
            );

            // Update local subscription record
            $subscription->update([
                'status'               => $stripeSubscription->status,
                'cancel_at_period_end' => $stripeSubscription->cancel_at_period_end,
                'canceled_at'          => $stripeSubscription->canceled_at ? Carbon::createFromTimestamp($stripeSubscription->canceled_at) : null,
            ]);

            return $subscription;
        });
    }

    /**
     * Resume a canceled subscription.
     *
     * @throws StripeException
     */
    public function resumeSubscription(Subscription $subscription): Subscription
    {
        return $this->handleStripeException(function () use ($subscription) {
            // Resume subscription in Stripe
            $stripeSubscription = $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => false]
            );

            // Update local subscription record
            $subscription->update([
                'status'               => $stripeSubscription->status,
                'cancel_at_period_end' => false,
                'canceled_at'          => null,
            ]);

            return $subscription;
        });
    }

    /**
     * Sync a Stripe subscription with our local database.
     *
     * @throws StripeException
     */
    public function syncSubscription(string $stripeSubscriptionId): Subscription
    {
        return $this->handleStripeException(function () use ($stripeSubscriptionId) {
            // Fetch subscription from Stripe
            $stripeSubscription = $this->stripe->subscriptions->retrieve($stripeSubscriptionId);

            // Find or create local subscription record
            $subscription = Subscription::firstOrNew([
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);

            if (!$subscription->exists) {
                // If this is a new subscription, we need the customer and plan
                $billingCustomer = BillingCustomer::where('stripe_customer_id', $stripeSubscription->customer)->first();

                if (!$billingCustomer) {
                    throw new StripeException('Cannot sync subscription: customer not found');
                }

                // Find the plan by looking up the price in the prices relation
                $plan = SubscriptionPlan::whereHas('prices', function ($query) use ($stripeSubscription) {
                    $query->where('stripe_price_id', $stripeSubscription->items->data[0]->price->id);
                })->first();

                if (!$plan) {
                    throw new StripeException('Cannot sync subscription: plan not found for price ' . $stripeSubscription->items->data[0]->price->id);
                }

                $subscription->billable()->associate($billingCustomer);
                $subscription->plan()->associate($plan);
            }

            // Update subscription data
            $subscription->fill([
                'status'               => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start ? Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                'current_period_end'   => $stripeSubscription->current_period_end ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                'trial_start'          => $stripeSubscription->trial_start ? Carbon::createFromTimestamp($stripeSubscription->trial_start) : null,
                'trial_end'            => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                'cancel_at_period_end' => $stripeSubscription->cancel_at_period_end,
                'canceled_at'          => $stripeSubscription->canceled_at ? Carbon::createFromTimestamp($stripeSubscription->canceled_at) : null,
            ]);

            $subscription->save();

            return $subscription;
        });
    }

    /**
     * Create a Stripe Checkout session for subscription.
     *
     * @param array $options Additional options for checkout session
     *
     * @throws StripeException
     */
    public function createCheckoutSession(
        BillingCustomer $billingCustomer,
        SubscriptionPlan $plan,
        BillingPrice $price,
        array $options = []
    ): CheckoutDataDTO {
        $this->billingCustomer = $billingCustomer;

        return $this->handleStripeException(function () use ($billingCustomer, $plan, $price, $options) {
            $checkoutData = [
                'customer'   => $billingCustomer->stripe_customer_id,
                'mode'       => 'subscription',
                'line_items' => [
                    [
                        'price'    => $price->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $this->buildCallbackUrl($options['success_url'] ?? config('app.url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'  => $this->buildCallbackUrl($options['cancel_url'] ?? config('app.url') . '/subscription/cancel'),
                'metadata'    => [
                    'plan_id' => $plan->id,
                ],
            ];

            // Add trial period if specified
            if (isset($options['trial_end'])) {
                $checkoutData['subscription_data'] = [
                    'trial_end' => $options['trial_end'],
                ];
            }

            $session = $this->stripe->checkout->sessions->create($checkoutData);

            return new CheckoutDataDTO(
                checkoutUrl: $session->url,
                sessionId: $session->id,
            );
        });
    }

    protected function buildCallbackUrl(string $url): string
    {
        /** @var User|Tenant $billable */
        $billable = $this->billingCustomer->billable;
        $tenantId = $billable->getTenantId();

        if (str_contains($url, '?')) {
            return $url . '&session_id={CHECKOUT_SESSION_ID}';
        }

        return $url . '?session_id={CHECKOUT_SESSION_ID}&tenant_id=' . $tenantId;
    }
}
