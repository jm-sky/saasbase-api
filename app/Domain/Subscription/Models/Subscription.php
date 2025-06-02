<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string            $id
 * @property string            $billable_type
 * @property string            $billable_id
 * @property ?string           $subscription_plan_id
 * @property string            $stripe_subscription_id
 * @property string            $status
 * @property Carbon            $current_period_start
 * @property Carbon            $current_period_end
 * @property ?Carbon           $ends_at
 * @property bool              $cancel_at_period_end
 * @property ?Model            $billable
 * @property ?SubscriptionPlan $plan
 */
class Subscription extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'subscription_plan_id',
        'stripe_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'ends_at',
        'cancel_at_period_end',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'ends_at'              => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
