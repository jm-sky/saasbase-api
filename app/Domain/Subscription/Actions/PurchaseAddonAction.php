<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Services\StripeAddonService;

/**
 * Action to purchase an addon (Stripe + local).
 */
class PurchaseAddonAction
{
    public function __construct(
        protected StripeAddonService $stripeAddonService
    ) {
    }

    /**
     * Purchase an addon (one-time or recurring).
     */
    public function __invoke(array $data, bool $recurring = false): string
    {
        // TODO: Validate data, call service, handle local model
        if ($recurring) {
            return $this->stripeAddonService->purchaseRecurringAddon($data);
        }

        return $this->stripeAddonService->purchaseOneTimeAddon($data);
    }
}
