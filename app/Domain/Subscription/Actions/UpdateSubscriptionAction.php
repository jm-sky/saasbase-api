<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\Events\SubscriptionUpdated;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     *
     * @param array{
     *     plan_id?: string,
     *     proration_behavior?: string,
     *     metadata?: array<string, mixed>
     * } $data
     *
     * @throws StripeException
     */
    public function __invoke(string $stripeSubscriptionId, array $data): void
    {
        try {
            DB::transaction(function () use ($stripeSubscriptionId, $data) {
                // Find subscription
                $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)
                    ->firstOrFail()
                ;

                // Update subscription in Stripe and locally
                $updatedSubscription = $this->stripeSubscriptionService->updateSubscription(
                    $subscription,
                    [
                        'plan_id'            => $data['plan_id'] ?? null,
                        'proration_behavior' => $data['proration_behavior'] ?? null,
                        'metadata'           => $data['metadata'] ?? [],
                    ]
                );

                // Dispatch event
                event(new SubscriptionUpdated($updatedSubscription));
            });
        } catch (\Exception $e) {
            Log::error('Failed to update subscription', [
                'error'           => $e->getMessage(),
                'subscription_id' => $stripeSubscriptionId,
                'data'            => $data,
            ]);

            throw new StripeException(message: 'Failed to update subscription: ' . $e->getMessage(), previous: $e);
        }
    }
}
