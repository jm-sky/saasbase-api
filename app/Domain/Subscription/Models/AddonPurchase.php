<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property string           $id
 * @property string           $billable_type
 * @property string           $billable_id
 * @property string           $addon_package_id
 * @property ?string          $stripe_invoice_item_id
 * @property Carbon           $purchased_at
 * @property ?Carbon          $expires_at
 * @property ?int             $quantity
 * @property ?float           $amount
 * @property ?string          $currency
 * @property ?string          $status
 * @property ?BillingCustomer $billable
 * @property ?AddonPackage    $addonPackage
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
        'quantity',
        'amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at'   => 'datetime',
        'quantity'     => 'integer',
        'amount'       => 'float',
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function billingCustomer()
    {
        return $this->morphTo('billable');
    }

    public function addonPackage()
    {
        return $this->belongsTo(AddonPackage::class, 'addon_package_id');
    }

    public function isActive(): bool
    {
        return 'active' === $this->status && (null === $this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return null !== $this->expires_at && $this->expires_at->isPast();
    }
}
