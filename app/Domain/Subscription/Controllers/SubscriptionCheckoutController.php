<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Requests\SubscriptionCheckoutRequest;
use App\Domain\Subscription\Resources\SubscriptionCheckoutResource;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Http\Response;

class SubscriptionCheckoutController
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService
    ) {
    }

    /**
     * Create a Stripe Checkout session for subscription.
     */
    public function __invoke(SubscriptionCheckoutRequest $request): SubscriptionCheckoutResource
    {
        $plan            = SubscriptionPlan::findOrFail($request->planId);
        $billingCustomer = $request->user()->billingCustomer;

        if (!$billingCustomer) {
            abort(Response::HTTP_NOT_FOUND, 'Billing customer not found');
        }

        $checkoutData = $this->stripeSubscriptionService->createCheckoutSession(
            $billingCustomer,
            $plan,
            [
                'success_url' => $request->successUrl,
                'cancel_url'  => $request->cancelUrl,
            ]
        );

        return new SubscriptionCheckoutResource($checkoutData);
    }
}
