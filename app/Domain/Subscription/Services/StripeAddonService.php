<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\AddonPackage;
use App\Domain\Subscription\Models\AddonPurchase;
use App\Domain\Subscription\Models\BillingCustomer;
use Carbon\Carbon;

/**
 * Service for managing Stripe addon purchases and local AddonPurchase records.
 */
class StripeAddonService extends StripeService
{
    /**
     * Purchase an addon for a customer.
     *
     * @param array $options Additional purchase options
     *
     * @throws StripeException
     */
    public function purchaseAddon(
        BillingCustomer $billingCustomer,
        AddonPackage $addon,
        array $options = []
    ): AddonPurchase {
        return $this->handleStripeException(function () use ($billingCustomer, $addon, $options) {
            // Create invoice item in Stripe
            $invoiceItem = $this->stripe->invoiceItems->create([
                'customer'     => $billingCustomer->stripe_customer_id,
                'price'        => $addon->stripe_price_id,
                'quantity'     => $options['quantity'] ?? 1,
                'description'  => $addon->name,
                'metadata'     => [
                    'addon_id' => $addon->id,
                ],
            ]);

            // Create local addon purchase record
            $purchase = new AddonPurchase([
                'stripe_invoice_item_id' => $invoiceItem->id,
                'quantity'               => $options['quantity'] ?? 1,
                'purchased_at'           => Carbon::now(),
                'expires_at'             => $this->calculateExpiryDate($addon, $options),
            ]);

            $purchase->addonPackage()->associate($addon);
            $billingCustomer->addonPurchases()->save($purchase);

            // If immediate payment is requested, create an invoice
            if ($options['immediate_payment'] ?? false) {
                $this->createInvoice($billingCustomer);
            }

            return $purchase;
        });
    }

    /**
     * Update an existing addon purchase.
     *
     * @param array $options Update options
     *
     * @throws StripeException
     */
    public function updateAddonPurchase(AddonPurchase $purchase, array $options): AddonPurchase
    {
        return $this->handleStripeException(function () use ($purchase, $options) {
            // Update invoice item in Stripe
            $this->stripe->invoiceItems->update($purchase->stripe_invoice_item_id, [
                'quantity' => $options['quantity'] ?? $purchase->quantity,
            ]);

            // Update local purchase record
            $purchase->update([
                'quantity'   => $options['quantity'] ?? $purchase->quantity,
                'expires_at' => $this->calculateExpiryDate($purchase->addonPackage, $options),
            ]);

            return $purchase;
        });
    }

    /**
     * Cancel an addon purchase.
     *
     * @throws StripeException
     */
    public function cancelAddonPurchase(AddonPurchase $purchase): bool
    {
        return $this->handleStripeException(function () use ($purchase) {
            // Delete invoice item in Stripe
            $this->stripe->invoiceItems->delete($purchase->stripe_invoice_item_id);

            // Delete local purchase record
            $purchase->delete();

            return true;
        });
    }

    /**
     * Sync an addon purchase with our local database.
     *
     * @throws StripeException
     */
    public function syncAddonPurchase(string $stripeInvoiceItemId): AddonPurchase
    {
        return $this->handleStripeException(function () use ($stripeInvoiceItemId) {
            // Fetch invoice item from Stripe
            $invoiceItem = $this->stripe->invoiceItems->retrieve($stripeInvoiceItemId);

            // Find or create local purchase record
            $purchase = AddonPurchase::firstOrNew([
                'stripe_invoice_item_id' => $invoiceItem->id,
            ]);

            if (!$purchase->exists) {
                // If this is a new purchase, we need the customer and addon
                $billingCustomer = BillingCustomer::where('stripe_customer_id', $invoiceItem->customer)->first();

                if (!$billingCustomer) {
                    throw new StripeException('Cannot sync addon purchase: customer not found');
                }

                $addon = AddonPackage::where('stripe_price_id', $invoiceItem->price)->first();

                if (!$addon) {
                    throw new StripeException('Cannot sync addon purchase: addon package not found');
                }

                $purchase->billingCustomer()->associate($billingCustomer);
                $purchase->addonPackage()->associate($addon);
            }

            // Update purchase data
            $purchase->fill([
                'quantity'     => $invoiceItem->quantity,
                'purchased_at' => Carbon::createFromTimestamp($invoiceItem->created),
                'expires_at'   => $this->calculateExpiryDate($purchase->addonPackage, [
                    'purchased_at' => $invoiceItem->created,
                ]),
            ]);

            $purchase->save();

            return $purchase;
        });
    }

    /**
     * Create an invoice for a customer.
     *
     * @throws StripeException
     */
    protected function createInvoice(BillingCustomer $billingCustomer): void
    {
        $this->handleStripeException(function () use ($billingCustomer) {
            $this->stripe->invoices->create([
                'customer'     => $billingCustomer->stripe_customer_id,
                'auto_advance' => true,
            ]);
        });
    }

    /**
     * Calculate the expiry date for an addon purchase.
     */
    protected function calculateExpiryDate(AddonPackage $addon, array $options): ?Carbon
    {
        if ('one-time' === $addon->type) {
            return null;
        }

        $purchasedAt = isset($options['purchased_at'])
            ? Carbon::createFromTimestamp($options['purchased_at'])
            : Carbon::now();

        return $purchasedAt->addDays($addon->duration_days);
    }
}
