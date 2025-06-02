<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Events\SubscriptionCancelled;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     *
     * @param bool $atPeriodEnd Whether to cancel at the end of the current period
     *
     * @throws StripeException
     */
    public function __invoke(string $stripeSubscriptionId, bool $atPeriodEnd = true): void
    {
        try {
            DB::transaction(function () use ($stripeSubscriptionId, $atPeriodEnd) {
                // Find subscription
                $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)
                    ->firstOrFail()
                ;

                // Cancel subscription in Stripe and locally
                $cancelledSubscription = $this->stripeSubscriptionService->cancelSubscription(
                    $subscription,
                    $atPeriodEnd
                );

                // Dispatch event
                event(new SubscriptionCancelled($cancelledSubscription));
            });
        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'error'           => $e->getMessage(),
                'subscription_id' => $stripeSubscriptionId,
                'at_period_end'   => $atPeriodEnd,
            ]);

            throw new StripeException(message: 'Failed to cancel subscription: ' . $e->getMessage(), previous: $e);
        }
    }
}
