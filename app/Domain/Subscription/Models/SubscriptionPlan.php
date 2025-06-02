<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Subscription\Enums\BillingInterval;
use App\Domain\Subscription\Enums\FeatureName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                    $id
 * @property string                    $name
 * @property ?string                   $description
 * @property string                    $stripe_product_id
 * @property string                    $stripe_price_id
 * @property BillingInterval           $interval
 * @property float                     $price
 * @property Collection|Subscription[] $subscriptions
 * @property Collection|PlanFeature[]  $planFeatures
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
    ];

    protected $casts = [
        'price'    => 'float',
        'interval' => BillingInterval::class,
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
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

    public function isMonthly(): bool
    {
        return BillingInterval::MONTHLY === $this->interval;
    }

    public function isQuarterly(): bool
    {
        return BillingInterval::QUARTERLY === $this->interval;
    }

    public function isYearly(): bool
    {
        return BillingInterval::YEARLY === $this->interval;
    }
}
