<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Events\AddonPurchased;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\AddonPackage;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Services\StripeAddonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     *
     * @param array{
     *     billing_customer_id: string,
     *     addon_id: string,
     *     quantity?: int,
     *     immediate_payment?: bool,
     *     metadata?: array<string, mixed>
     * } $data
     *
     * @throws StripeException
     */
    public function __invoke(array $data): string
    {
        try {
            return DB::transaction(function () use ($data) {
                // Find required models
                $billingCustomer = BillingCustomer::findOrFail($data['billing_customer_id']);
                $addon           = AddonPackage::findOrFail($data['addon_id']);

                // Purchase addon in Stripe and locally
                $purchase = $this->stripeAddonService->purchaseAddon(
                    $billingCustomer,
                    $addon,
                    [
                        'quantity'          => $data['quantity'] ?? 1,
                        'immediate_payment' => $data['immediate_payment'] ?? false,
                        'metadata'          => $data['metadata'] ?? [],
                    ]
                );

                // Dispatch event
                event(new AddonPurchased($purchase));

                return $purchase->stripe_invoice_item_id;
            });
        } catch (\Exception $e) {
            Log::error('Failed to purchase addon', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);

            throw new StripeException(message: 'Failed to purchase addon: ' . $e->getMessage(), previous: $e);
        }
    }
}
