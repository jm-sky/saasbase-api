<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string  $id
 * @property string  $billable_type
 * @property string  $billable_id
 * @property string  $stripe_invoice_id
 * @property float   $amount_due
 * @property string  $status
 * @property string  $hosted_invoice_url
 * @property string  $pdf_url
 * @property Carbon  $issued_at
 * @property ?Carbon $paid_at
 * @property ?Model  $billable
 */
class SubscriptionInvoice extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'stripe_invoice_id',
        'amount_due',
        'status',
        'hosted_invoice_url',
        'pdf_url',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'amount_due' => 'float',
        'issued_at'  => 'datetime',
        'paid_at'    => 'datetime',
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}
