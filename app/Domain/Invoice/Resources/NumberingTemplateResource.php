<?php

namespace App\Domain\Invoice\Resources;

use App\Domain\Invoice\Models\NumberingTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NumberingTemplateResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /* @var NumberingTemplate $this->resource */
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'name'         => $this->name,
            'invoiceType'  => $this->invoice_type?->value,
            'format'       => $this->format,
            'nextNumber'   => $this->next_number,
            'resetPeriod'  => $this->reset_period?->value,
            'prefix'       => $this->prefix,
            'suffix'       => $this->suffix,
            'isDefault'    => $this->is_default,
            'createdAt'    => $this->created_at?->toIso8601String(),
            'updatedAt'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
