<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Financial\Models\PKWiUClassification
 */
class PKWiUClassificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'          => $this->code,
            'parentCode'    => $this->parent_code,
            'name'          => $this->name,
            'description'   => $this->description,
            'level'         => $this->level,
            'isActive'      => $this->is_active,
            'hierarchyPath' => $this->getFullHierarchyPath(),
            'isLeaf'        => $this->isLeafNode(),
            'children'      => self::collection($this->whenLoaded('children')),
            'parent'        => new self($this->whenLoaded('parent')),
            'createdAt'     => $this->created_at?->toIso8601String(),
            'updatedAt'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
