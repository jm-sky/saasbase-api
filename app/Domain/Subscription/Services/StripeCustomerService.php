<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\BillingInfo;
use Illuminate\Database\Eloquent\Model;
use Stripe\Customer;

/**
 * Service for managing Stripe customers and syncing billing info.
 */
class StripeCustomerService extends StripeService
{
    /**
     * Create a new Stripe customer and link it to a billable model.
     *
     * @param Model $billable The model that can be billed (User/Tenant)
     * @param array $data     Customer data (email, name, etc.)
     *
     * @throws StripeException
     */
    public function createCustomer(Model $billable, array $data): BillingCustomer
    {
        return $this->handleStripeException(function () use ($billable, $data) {
            // Create customer in Stripe
            $stripeCustomer = $this->stripe->customers->create([
                'email'    => $data['email'] ?? $billable->email,
                'name'     => $data['name'] ?? $billable->name,
                'metadata' => [
                    'billable_type' => get_class($billable),
                    'billable_id'   => $billable->id,
                ],
            ]);

            // Create local billing customer record
            $billingCustomer = new BillingCustomer([
                'stripe_customer_id' => $stripeCustomer->id,
            ]);

            $billable->billingCustomer()->save($billingCustomer);

            // If billing info is provided, create it
            if (isset($data['billing_info'])) {
                $this->updateBillingInfo($billingCustomer, $data['billing_info']);
            }

            return $billingCustomer;
        });
    }

    /**
     * Update an existing Stripe customer.
     *
     * @param array $data Customer data to update
     *
     * @throws StripeException
     */
    public function updateCustomer(BillingCustomer $billingCustomer, array $data): BillingCustomer
    {
        return $this->handleStripeException(function () use ($billingCustomer, $data) {
            // Update customer in Stripe
            $this->stripe->customers->update($billingCustomer->stripe_customer_id, [
                'email' => $data['email'] ?? null,
                'name'  => $data['name'] ?? null,
            ]);

            // Update local billing info if provided
            if (isset($data['billing_info'])) {
                $this->updateBillingInfo($billingCustomer, $data['billing_info']);
            }

            return $billingCustomer;
        });
    }

    /**
     * Update billing information for a customer.
     *
     * @param array $data Billing information
     *
     * @throws StripeException
     */
    public function updateBillingInfo(BillingCustomer $billingCustomer, array $data): BillingInfo
    {
        return $this->handleStripeException(function () use ($billingCustomer, $data) {
            // Update billing info in Stripe
            $this->stripe->customers->update($billingCustomer->stripe_customer_id, [
                'address' => [
                    'line1'       => $data['address_line1'] ?? null,
                    'line2'       => $data['address_line2'] ?? null,
                    'city'        => $data['city'] ?? null,
                    'state'       => $data['state'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'country'     => $data['country'] ?? null,
                ],
                'shipping' => [
                    'name'    => $data['name'] ?? null,
                    'address' => [
                        'line1'       => $data['address_line1'] ?? null,
                        'line2'       => $data['address_line2'] ?? null,
                        'city'        => $data['city'] ?? null,
                        'state'       => $data['state'] ?? null,
                        'postal_code' => $data['postal_code'] ?? null,
                        'country'     => $data['country'] ?? null,
                    ],
                ],
                'tax' => [
                    'ip_address' => $data['tax_id'] ?? null,
                ],
            ]);

            // Create or update local billing info
            $billingInfo = $billingCustomer->billingInfo ?? new BillingInfo();
            $billingInfo->fill([
                'name'          => $data['name'] ?? null,
                'email'         => $data['email'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'address_line2' => $data['address_line2'] ?? null,
                'city'          => $data['city'] ?? null,
                'state'         => $data['state'] ?? null,
                'postal_code'   => $data['postal_code'] ?? null,
                'country'       => $data['country'] ?? null,
                'tax_id'        => $data['tax_id'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);

            $billingCustomer->billingInfo()->save($billingInfo);

            return $billingInfo;
        });
    }

    /**
     * Delete a Stripe customer and associated local records.
     *
     * @throws StripeException
     */
    public function deleteCustomer(BillingCustomer $billingCustomer): bool
    {
        return $this->handleStripeException(function () use ($billingCustomer) {
            // Delete customer in Stripe
            $this->stripe->customers->delete($billingCustomer->stripe_customer_id);

            // Delete local records
            $billingCustomer->billingInfo?->delete();
            $billingCustomer->delete();

            return true;
        });
    }

    /**
     * Sync a Stripe customer with our local database.
     *
     * @throws StripeException
     */
    public function syncCustomer(string $stripeCustomerId): BillingCustomer
    {
        return $this->handleStripeException(function () use ($stripeCustomerId) {
            // Fetch customer from Stripe
            $stripeCustomer = $this->stripe->customers->retrieve($stripeCustomerId);

            // Find or create local billing customer
            $billingCustomer = BillingCustomer::firstOrNew([
                'stripe_customer_id' => $stripeCustomer->id,
            ]);

            if (!$billingCustomer->exists) {
                // If this is a new customer, we need the billable model
                if (!isset($stripeCustomer->metadata->billable_type) || !isset($stripeCustomer->metadata->billable_id)) {
                    throw new StripeException('Cannot sync customer: missing billable information in Stripe metadata');
                }

                $billableClass = $stripeCustomer->metadata->billable_type;
                $billableId    = $stripeCustomer->metadata->billable_id;

                $billable = $billableClass::find($billableId);

                if (!$billable) {
                    throw new StripeException('Cannot sync customer: billable model not found');
                }

                $billable->billingCustomer()->save($billingCustomer);
            }

            // Sync billing info
            if ($stripeCustomer->address || $stripeCustomer->shipping) {
                $billingInfo = $billingCustomer->billingInfo ?? new BillingInfo();

                $address = $stripeCustomer->address ?? $stripeCustomer->shipping->address;
                $name    = $stripeCustomer->name ?? $stripeCustomer->shipping->name;

                $billingInfo->fill([
                    'name'          => $name,
                    'email'         => $stripeCustomer->email,
                    'address_line1' => $address->line1,
                    'address_line2' => $address->line2,
                    'city'          => $address->city,
                    'state'         => $address->state,
                    'postal_code'   => $address->postal_code,
                    'country'       => $address->country,
                    'tax_id'        => $stripeCustomer->tax->ip_address ?? null,
                ]);

                $billingCustomer->billingInfo()->save($billingInfo);
            }

            return $billingCustomer;
        });
    }
}
