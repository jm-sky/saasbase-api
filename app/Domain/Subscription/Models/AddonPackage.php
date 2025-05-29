<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;

/**
 * @property string                                                   $id
 * @property string                                                   $name
 * @property string                                                   $stripe_price_id
 * @property string                                                   $description
 * @property string                                                   $type
 * @property ?float                                                   $price
 * @property \Illuminate\Database\Eloquent\Collection|AddonPurchase[] $purchases
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
    ];

    public function purchases()
    {
        return $this->hasMany(AddonPurchase::class, 'addon_package_id');
    }
}
