<?php

namespace App\Domain\Subscription\Services;

/**
 * Service for managing Stripe subscriptions and local Subscription records.
 */
class StripeSubscriptionService
{
    /**
     * Create a new Stripe subscription and store in Subscription.
     */
    public function createSubscription(array $data): string
    {
        // TODO: Implement Stripe API call and local record creation
        return '';
    }

    /**
     * Update an existing Stripe subscription (e.g., plan change).
     */
    public function updateSubscription(string $stripeSubscriptionId, array $data): void
    {
        // TODO: Implement Stripe API call and local record update
    }

    /**
     * Cancel a Stripe subscription (immediate or at period end).
     */
    public function cancelSubscription(string $stripeSubscriptionId, bool $atPeriodEnd = true): void
    {
        // TODO: Implement Stripe API call and local record update
    }
}
