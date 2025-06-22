<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string                    $id
 * @property string                    $billable_type
 * @property string                    $billable_id
 * @property string                    $stripe_customer_id
 * @property Tenant|User               $billable
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 * @property Collection<AddonPurchase> $addonPurchases
 * @property Collection<Subscription>  $subscriptions
 */
class BillingCustomer extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'stripe_customer_id',
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function addonPurchases()
    {
        return $this->morphMany(AddonPurchase::class, 'billable');
    }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'billable');
    }
}
