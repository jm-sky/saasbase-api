<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Billing\Models\BillingPrice;
use App\Domain\Subscription\Models\BillingCustomer;
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
        $tenantId        = $user->getTenantId();
        $tenant          = Tenant::findOrFail($tenantId);
        $plan            = SubscriptionPlan::findOrFail($request->planId);
        $price           = $this->provideBillingPrice($plan, $request->priceId);
        $billingCustomer = $this->provideBillingCustomer($user, $tenant, $request->billableType);

        $checkoutData = $this->stripeSubscriptionService->createCheckoutSession(
            $billingCustomer,
            $plan,
            $price,
            [
                'success_url' => $request->successUrl,
                'cancel_url'  => $request->cancelUrl,
            ]
        );

        return new SubscriptionCheckoutResource($checkoutData);
    }

    protected function provideBillingCustomer(User $user, Tenant $tenant, string $billableType): BillingCustomer
    {
        if ('tenant' === $billableType) {
            return $tenant->billingCustomer ?? $this->stripeCustomerService->createCustomer($tenant, [
                'email' => $tenant->email ?? $user->email,
                'name'  => $tenant->name ?? $user->name,
            ]);
        }

        if ('user' === $billableType) {
            return $this->stripeCustomerService->createCustomer($user, [
                'email' => $user->email,
                'name'  => $user->name,
            ]);
        }

        throw new \Exception('Invalid billable type');
    }

    protected function provideBillingPrice(SubscriptionPlan $plan, string $priceId): BillingPrice
    {
        // @phpstan-ignore-next-line
        return $plan->prices()
            ->where('id', $priceId)
            ->where('is_active', true)
            ->firstOrFail()
        ;
    }
}
