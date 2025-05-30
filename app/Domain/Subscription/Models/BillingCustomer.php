<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;

/**
 * @property string                                   $id
 * @property string                                   $billable_type
 * @property string                                   $billable_id
 * @property string                                   $stripe_customer_id
 * @property \Illuminate\Database\Eloquent\Model|null $billable
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
}
