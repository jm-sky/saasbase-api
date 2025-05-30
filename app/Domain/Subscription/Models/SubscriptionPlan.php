<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;

/**
 * @property string                                                  $id
 * @property string                                                  $name
 * @property ?string                                                 $description
 * @property string                                                  $stripe_product_id
 * @property string                                                  $stripe_price_id
 * @property string                                                  $interval
 * @property float                                                   $price
 * @property ?array                                                  $features
 * @property \Illuminate\Database\Eloquent\Collection|Subscription[] $subscriptions
 */
class SubscriptionPlan extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'stripe_product_id',
        'stripe_price_id',
        'interval',
        'price',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
        'price'    => 'float',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }
}
