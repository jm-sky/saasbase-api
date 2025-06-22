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
 * @property Collection|PlanFeature[]  $features
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

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(BillingPrice::class, 'priceable');
    }

    public function isCurrent(string $tenantId): bool
    {
        return $this->subscriptions()
            ->whereHas('billable', function ($query) use ($tenantId) {
                $query->where('id', $tenantId);
            })
            ->where('status', 'active')
            ->exists()
        ;
    }

    public function getFeature(FeatureName $feature)
    {
        /** @var ?PlanFeature $planFeature */
        $planFeature = $this->features()
            ->whereHas('feature', function ($query) use ($feature) {
                $query->where('name', $feature->value);
            })
            ->first()
        ;

        return $planFeature?->value;
    }

    public function hasFeature(FeatureName $feature): bool
    {
        return $this->features()
            ->whereHas('feature', function ($query) use ($feature) {
                $query->where('name', $feature->value);
            })
            ->exists()
        ;
    }

    public function setFeature(FeatureName $feature, $value): void
    {
        /** @var ?Feature $featureModel */
        $featureModel = Feature::where('name', $feature->value)->first();

        if (!$featureModel) {
            throw new \InvalidArgumentException("Feature {$feature->value} does not exist");
        }

        $this->features()->updateOrCreate(
            ['feature_id' => $featureModel->id],
            ['value' => $value]
        );
    }

    public function getAllFeatures(): array
    {
        return $this->features()
            ->with('feature')
            ->get()
            ->mapWithKeys(function (PlanFeature $planFeature) {
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
