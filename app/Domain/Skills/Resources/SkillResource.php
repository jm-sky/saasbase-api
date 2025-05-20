<?php

namespace App\Domain\Skills\Resources;

use App\Domain\Skills\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Skill $this->resource */
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'category'    => $this->category,
            'description' => $this->description,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'deletedAt'   => $this->deleted_at?->toIso8601String(),
        ];
    }
}
