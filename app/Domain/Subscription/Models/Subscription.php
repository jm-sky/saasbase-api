<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string             $id
 * @property string             $billable_type
 * @property string             $billable_id
 * @property ?string            $subscription_plan_id
 * @property string             $stripe_subscription_id
 * @property SubscriptionStatus $status
 * @property Carbon             $current_period_start
 * @property Carbon             $current_period_end
 * @property ?Carbon            $ends_at
 * @property bool               $cancel_at_period_end
 * @property ?Carbon            $canceled_at
 * @property ?Model             $billable
 * @property ?SubscriptionPlan  $plan
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
        'canceled_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'ends_at'              => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at'          => 'datetime',
        'status'               => SubscriptionStatus::class,
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCanceled(): bool
    {
        return SubscriptionStatus::CANCELED === $this->status;
    }

    public function isPastDue(): bool
    {
        return SubscriptionStatus::PAST_DUE === $this->status;
    }

    public function isOnTrial(): bool
    {
        return SubscriptionStatus::TRIALING === $this->status;
    }
}
