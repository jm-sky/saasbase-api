<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\OrganizationUnit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrganizationUnit
 */
class OrganizationUnitPreviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
