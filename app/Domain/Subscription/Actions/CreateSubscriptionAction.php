<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Services\StripeSubscriptionService;

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
     */
    public function __invoke(array $data): string
    {
        // TODO: Validate data, call service, handle local model
        return $this->stripeSubscriptionService->createSubscription($data);
    }
}
