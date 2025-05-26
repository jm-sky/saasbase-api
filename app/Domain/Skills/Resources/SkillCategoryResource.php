<?php

namespace App\Domain\Skills\Resources;

use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /* @var SkillCategory $this */
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'createdAt'   => $this->created_at->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'skills'      => SkillResource::collection($this->whenLoaded('skills')),
        ];
    }
}
