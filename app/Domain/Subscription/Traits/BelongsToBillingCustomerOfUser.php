<?php

namespace App\Domain\Subscription\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Models\BillingCustomer;
use Illuminate\Database\Eloquent\Builder;

/**
 * For Subscription, SubscriptionInvoice, and AddonPurchase, whose `billable`
 * (polymorphic) always points at a BillingCustomer, not directly at a Tenant
 * or User. Without this, index/show/update/destroy had no ownership check at
 * all — any authenticated user could read or modify any tenant's
 * subscriptions, invoices, and addon purchases.
 */
trait BelongsToBillingCustomerOfUser
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query
            ->where('billable_type', BillingCustomer::class)
            ->whereIn('billable_id', BillingCustomer::query()->forUser($user)->select('id'));
    }
}
