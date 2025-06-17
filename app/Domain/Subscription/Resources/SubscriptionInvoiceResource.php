<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\Enums\SubscriptionInvoiceStatus;
use App\Domain\Subscription\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubscriptionInvoice
 */
final class SubscriptionInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'number'           => $this->number,
            'status'           => $this->status,
            'statusLabel'      => SubscriptionInvoiceStatus::from($this->status)->label(),
            'amount'           => $this->amount,
            'currency'         => $this->currency,
            'dueDate'          => $this->due_date,
            'paidAt'           => $this->paid_at,
            'hostedInvoiceUrl' => $this->hosted_invoice_url,
            'invoicePdf'       => $this->invoice_pdf,
            'items'            => $this->items,
            'createdAt'        => $this->created_at,
            'updatedAt'        => $this->updated_at,
        ];
    }
}
