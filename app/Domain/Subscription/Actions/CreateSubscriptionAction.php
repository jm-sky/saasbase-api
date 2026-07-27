<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Subscription\DTOs\CreateSubscriptionDTO;
use App\Domain\Subscription\DTOs\CreateSubscriptionOptionsDTO;
use App\Domain\Subscription\Events\SubscriptionCreated;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Services\StripePaymentService;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action to create a new subscription (Stripe + local).
 */
class CreateSubscriptionAction
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService,
        protected StripePaymentService $stripePaymentService
    ) {}

    /**
     * Create a new subscription for a billable entity.
     *
     * @throws StripeException
     */
    public function __invoke(CreateSubscriptionDTO $data): string
    {
        try {
            return DB::transaction(function () use ($data) {
                // Find required models
                /** @var BillingCustomer $billingCustomer */
                $billingCustomer = BillingCustomer::findOrFail($data->billingCustomerId);
                /** @var SubscriptionPlan $plan */
                $plan = SubscriptionPlan::findOrFail($data->planId);

                // Attach payment method if provided
                if (isset($data->paymentDetails)) {
                    $this->stripePaymentService->attachPaymentMethod($billingCustomer, $data->paymentDetails);
                }

                // Create subscription in Stripe and locally
                $subscription = $this->stripeSubscriptionService->createSubscription(
                    $billingCustomer,
                    $plan,
                    CreateSubscriptionOptionsDTO::fromArray($data->toArray())
                );

                // Dispatch event
                event(new SubscriptionCreated($subscription));

                return $subscription->stripe_subscription_id;
            });
        } catch (\Exception $e) {
            // Never log the full DTO: even without raw card data, it carries a
            // Stripe payment method id and customer/plan references that don't
            // belong in application logs.
            Log::error('Failed to create subscription', [
                'error' => $e->getMessage(),
                'billing_customer_id' => $data->billingCustomerId,
                'plan_id' => $data->planId,
            ]);

            throw new StripeException(message: 'Failed to create subscription: '.$e->getMessage(), previous: $e);
        }
    }
}
