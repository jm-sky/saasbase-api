<?php

namespace App\Domain\Subscription\Services;

/**
 * Service for managing Stripe addon purchases and local AddonPurchase records.
 */
class StripeAddonService
{
    /**
     * Process a one-time addon purchase via Stripe and store in AddonPurchase.
     */
    public function purchaseOneTimeAddon(array $data): string
    {
        // TODO: Implement Stripe API call and local record creation
        return '';
    }

    /**
     * Process a recurring addon purchase via Stripe and store in AddonPurchase.
     */
    public function purchaseRecurringAddon(array $data): string
    {
        // TODO: Implement Stripe API call and local record creation
        return '';
    }

    /**
     * Handle expiration of an addon purchase.
     */
    public function expireAddon(string $addonPurchaseId): void
    {
        // TODO: Implement expiration logic
    }
}
