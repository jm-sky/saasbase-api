<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Billing\Models\BillingPrice;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Subscription\Enums\BillingInterval;
use App\Domain\Subscription\Enums\FeatureName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string                    $id
 * @property string                    $name
 * @property ?string                   $description
 * @property string                    $stripe_product_id
 * @property bool                      $is_active
 * @property Collection|Subscription[] $subscriptions
 * @property Collection|PlanFeature[]  $planFeatures
 * @property Collection|BillingPrice[] $prices
 */
class SubscriptionPlan extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'stripe_product_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(BillingPrice::class, 'priceable');
    }

    public function getFeature(FeatureName $feature)
    {
        $planFeature = $this->planFeatures()
            ->whereHas('feature', function ($query) use ($feature) {
                $query->where('name', $feature->value);
            })
            ->first()
        ;

        return $planFeature?->value;
    }

    public function hasFeature(FeatureName $feature): bool
    {
        return $this->planFeatures()
            ->whereHas('feature', function ($query) use ($feature) {
                $query->where('name', $feature->value);
            })
            ->exists()
        ;
    }

    public function setFeature(FeatureName $feature, $value): void
    {
        $featureModel = Feature::where('name', $feature->value)->first();

        if (!$featureModel) {
            throw new \InvalidArgumentException("Feature {$feature->value} does not exist");
        }

        $this->planFeatures()->updateOrCreate(
            ['feature_id' => $featureModel->id],
            ['value' => $value]
        );
    }

    public function getAllFeatures(): array
    {
        return $this->planFeatures()
            ->with('feature')
            ->get()
            ->mapWithKeys(function ($planFeature) {
                return [$planFeature->feature->name => $planFeature->value];
            })
            ->toArray()
        ;
    }

    public function getPriceForInterval(BillingInterval $interval): ?BillingPrice
    {
        return $this->prices()
            ->where('billing_period', $interval->value)
            ->where('is_active', true)
            ->first()
        ;
    }
}
