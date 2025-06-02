<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string  $id
 * @property string  $subscription_plan_id
 * @property string  $feature_id
 * @property string  $value
 * @property Feature $feature
 */
class PlanFeature extends BaseModel
{
    protected $fillable = [
        'subscription_plan_id',
        'feature_id',
        'value',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function getValueAttribute($value)
    {
        return $this->feature->getValueAttribute($value);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->feature->setValueAttribute($value);
    }
}
