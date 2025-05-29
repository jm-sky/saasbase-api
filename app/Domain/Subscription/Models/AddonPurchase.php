<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property string                                   $id
 * @property string                                   $billable_type
 * @property string                                   $billable_id
 * @property string                                   $addon_package_id
 * @property ?string                                  $stripe_invoice_item_id
 * @property Carbon                                   $purchased_at
 * @property ?Carbon                                  $expires_at
 * @property \Illuminate\Database\Eloquent\Model|null $billable
 * @property AddonPackage|null                        $package
 */
class AddonPurchase extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'addon_package_id',
        'stripe_invoice_item_id',
        'purchased_at',
        'expires_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function package()
    {
        return $this->belongsTo(AddonPackage::class, 'addon_package_id');
    }
}
