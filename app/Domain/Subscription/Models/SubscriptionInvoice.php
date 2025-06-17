<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Subscription\Enums\SubscriptionInvoiceStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string                    $id
 * @property string                    $billable_type
 * @property string                    $billable_id
 * @property string                    $stripe_invoice_id
 * @property string                    $number
 * @property float                     $amount_due
 * @property float                     $amount
 * @property string                    $currency
 * @property SubscriptionInvoiceStatus $status
 * @property string                    $hosted_invoice_url
 * @property string                    $pdf_url
 * @property string                    $invoice_pdf
 * @property Carbon                    $issued_at
 * @property ?Carbon                   $due_date
 * @property ?Carbon                   $paid_at
 * @property array                     $items
 * @property ?Model                    $billable
 */
class SubscriptionInvoice extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'stripe_invoice_id',
        'number',
        'amount_due',
        'amount',
        'currency',
        'status',
        'hosted_invoice_url',
        'pdf_url',
        'invoice_pdf',
        'issued_at',
        'due_date',
        'paid_at',
        'items',
    ];

    protected $casts = [
        'amount_due' => 'float',
        'amount'     => 'float',
        'issued_at'  => 'datetime',
        'due_date'   => 'datetime',
        'paid_at'    => 'datetime',
        'status'     => SubscriptionInvoiceStatus::class,
        'items'      => 'array',
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function isVoid(): bool
    {
        return $this->status->isVoid();
    }

    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    public function needsAttention(): bool
    {
        return $this->status->needsAttention();
    }
}
