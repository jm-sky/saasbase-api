<?php

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Enums\BillingPeriod;
use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string        $id              UUID of the price
 * @property string        $priceable_id    UUID of the related model (SubscriptionPlan or Addon)
 * @property string        $priceable_type  Class name of the related model
 * @property string        $stripe_price_id Stripe Price ID
 * @property BillingPeriod $billing_period  Billing period ('monthly' or 'yearly')
 * @property int           $price_cents     Price in cents
 * @property string        $currency        Currency code (e.g., 'PLN')
 * @property bool          $is_active       Whether the price is currently active
 * @property Carbon        $created_at      Creation timestamp
 * @property Carbon        $updated_at      Last update timestamp
 * @property float         $price           Price in decimal format (price_cents / 100)
 * @property Model         $priceable       Related model (SubscriptionPlan or Addon)
 */
class BillingPrice extends BaseModel
{
    protected $fillable = [
        'stripe_price_id',
        'billing_period',
        'price_cents',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'price_cents'    => 'integer',
        'is_active'      => 'boolean',
        'billing_period' => BillingPeriod::class,
    ];

    /**
     * Get the parent priceable model (SubscriptionPlan or Addon).
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the price in decimal format.
     */
    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }
}
