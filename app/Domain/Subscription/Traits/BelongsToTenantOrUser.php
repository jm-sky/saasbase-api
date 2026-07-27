<?php

namespace App\Domain\Subscription\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * For BillingCustomer, whose `billable` (polymorphic) points directly at a
 * Tenant or a User. Without this, index/show on BillingCustomer had no
 * ownership check — any authenticated user could read any tenant's billing
 * customer record.
 */
trait BelongsToTenantOrUser
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        $tenantId = $user->getTenantId();

        return $query->where(function (Builder $q) use ($user, $tenantId) {
            if ($tenantId) {
                $q->orWhere(function (Builder $q2) use ($tenantId) {
                    $q2->where('billable_type', Tenant::class)->where('billable_id', $tenantId);
                });
            }

            $q->orWhere(function (Builder $q2) use ($user) {
                $q2->where('billable_type', User::class)->where('billable_id', $user->id);
            });
        });
    }
}
