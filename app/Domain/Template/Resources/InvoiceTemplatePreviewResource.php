<?php

namespace App\Domain\Template\Resources;

use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InvoiceTemplate
 */
class InvoiceTemplatePreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category?->value,
            'settings'    => $this->settings,
            'isActive'    => $this->is_active,
            'isDefault'   => $this->is_default,
        ];
    }
}
