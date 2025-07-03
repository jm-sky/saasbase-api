<?php

namespace App\Domain\Invoice\Resources;

use App\Domain\Common\Resources\TagResource;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tenantId'            => $this->tenant_id,
            'type'                => $this->type->value,
            'status'              => $this->status->value, // Backward compatibility
            'statusInfo'          => [
                'general'    => $this->status?->value,
                'ocr'        => $this->ocr_status?->value,
                'allocation' => $this->allocation_status?->value,
                'approval'   => $this->approval_status?->value,
                'delivery'   => $this->delivery_status?->value,
                'payment'    => $this->payment_status?->value,
            ],
            'number'              => $this->number,
            'numberingTemplateId' => $this->numbering_template_id,
            'totalNet'            => $this->total_net->toFloat(),
            'totalTax'            => $this->total_tax->toFloat(),
            'totalGross'          => $this->total_gross->toFloat(),
            'currency'            => $this->currency,
            'exchangeRate'        => $this->exchange_rate->toFloat(),
            'seller'              => $this->seller->toArray(),
            'buyer'               => $this->buyer->toArray(),
            'body'                => $this->body->toArray(),
            'payment'             => $this->payment->toArray(),
            'options'             => $this->options->toArray(),
            'issueDate'           => $this->issue_date?->toDateString(),
            'createdAt'           => $this->created_at?->toIso8601String(),
            'updatedAt'           => $this->updated_at?->toIso8601String(),
            'numberingTemplate'   => $this->whenLoaded('numberingTemplate', fn () => $this->numberingTemplate->toArray()),
            'tags'                => TagResource::collection($this->tags),
        ];
    }
}
