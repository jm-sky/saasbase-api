<?php

namespace App\Domain\Subscription\Services;

/**
 * Service for managing Stripe customers and syncing billing info.
 */
class StripeCustomerService
{
    /**
     * Create a new Stripe customer and store in BillingCustomer.
     */
    public function createCustomer(array $data): string
    {
        // TODO: Implement Stripe API call and local record creation
        return '';
    }

    /**
     * Update an existing Stripe customer with new billing info.
     */
    public function updateCustomer(string $stripeCustomerId, array $data): void
    {
        // TODO: Implement Stripe API call and local record update
    }

    /**
     * Delete a Stripe customer and local record.
     */
    public function deleteCustomer(string $stripeCustomerId): void
    {
        // TODO: Implement Stripe API call and local record deletion
    }

    /**
     * Sync billing info from BillingInfo to Stripe.
     */
    public function syncBillingInfo(string $stripeCustomerId, array $billingInfo): void
    {
        // TODO: Implement Stripe API call for billing info
    }
}
