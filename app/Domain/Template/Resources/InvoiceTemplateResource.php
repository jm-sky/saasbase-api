<?php

namespace App\Domain\Template\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InvoiceTemplate
 */
class InvoiceTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenant_id,
            'userId'      => $this->user_id,
            'name'        => $this->name,
            'description' => $this->description,
            'content'     => $this->content,
            'category'    => $this->category?->value,
            'previewData' => $this->preview_data,
            'settings'    => $this->settings,
            'isActive'    => $this->is_active,
            'isDefault'   => $this->is_default,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'user'        => $this->whenLoaded('user', fn () => new UserPreviewResource($this->user)),
        ];
    }
}
