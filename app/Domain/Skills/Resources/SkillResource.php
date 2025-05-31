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
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /* @var Skill $this */
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category,
            'createdAt'   => $this->created_at->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
