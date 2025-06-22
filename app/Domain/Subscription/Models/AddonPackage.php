<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Subscription\Enums\AddonType;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string                     $id
 * @property string                     $name
 * @property string                     $stripe_price_id
 * @property string                     $description
 * @property AddonType                  $type
 * @property ?float                     $price
 * @property ?int                       $duration_days
 * @property Collection|AddonPurchase[] $purchases
 */
class AddonPackage extends BaseModel
{
    protected $fillable = [
        'name',
        'stripe_price_id',
        'description',
        'type',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
        'type'  => AddonType::class,
    ];

    public function purchases()
    {
        return $this->hasMany(AddonPurchase::class, 'addon_package_id');
    }

    public function isRecurring(): bool
    {
        return $this->type->isRecurring();
    }

    public function isOneTime(): bool
    {
        return $this->type->isOneTime();
    }

    public function isUsageBased(): bool
    {
        return $this->type->isUsageBased();
    }
}
