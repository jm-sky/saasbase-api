<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Services\StripeSubscriptionService;

/**
 * Action to cancel a subscription (Stripe + local).
 */
class CancelSubscriptionAction
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService
    ) {
    }

    /**
     * Cancel a subscription (immediate or at period end).
     */
    public function __invoke(string $stripeSubscriptionId, bool $atPeriodEnd = true): void
    {
        // TODO: Validate, call service, handle local model
        $this->stripeSubscriptionService->cancelSubscription($stripeSubscriptionId, $atPeriodEnd);
    }
}
