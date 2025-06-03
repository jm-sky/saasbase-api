<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Requests\SubscriptionCheckoutRequest;
use App\Domain\Subscription\Resources\SubscriptionCheckoutResource;
use App\Domain\Subscription\Services\StripeCustomerService;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class SubscriptionCheckoutController
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService,
        protected StripeCustomerService $stripeCustomerService
    ) {
    }

    /**
     * Create a Stripe Checkout session for subscription.
     */
    public function __invoke(SubscriptionCheckoutRequest $request): SubscriptionCheckoutResource
    {
        /** @var User $user */
        $user            = Auth::user();
        $tenantId        = $user->tenant_id;
        $tenant          = Tenant::findOrFail($tenantId);
        $plan            = SubscriptionPlan::findOrFail($request->planId);
        $billingCustomer = $tenant->billingCustomer;

        if (!$billingCustomer && 'tenant' === $request->billableType) {
            $billingCustomer = $this->stripeCustomerService->createCustomer($tenant, [
                'email' => $tenant->email ?? $user->email,
                'name'  => $tenant->name ?? $user->name,
            ]);
        } elseif (!$billingCustomer && 'user' === $request->billableType) {
            $billingCustomer = $this->stripeCustomerService->createCustomer($user, [
                'email' => $user->email,
                'name'  => $user->name,
            ]);
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
