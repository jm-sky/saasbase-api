<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Services\StripeSubscriptionService;

/**
 * Action to update an existing subscription (Stripe + local).
 */
class UpdateSubscriptionAction
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService
    ) {
    }

    /**
     * Update a subscription (e.g., plan change).
     */
    public function __invoke(string $stripeSubscriptionId, array $data): void
    {
        // TODO: Validate data, call service, handle local model
        $this->stripeSubscriptionService->updateSubscription($stripeSubscriptionId, $data);
    }
}
