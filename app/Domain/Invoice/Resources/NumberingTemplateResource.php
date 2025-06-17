<?php

namespace App\Domain\Invoice\Resources;

use App\Domain\Invoice\Models\NumberingTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NumberingTemplate
 */
class NumberingTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
