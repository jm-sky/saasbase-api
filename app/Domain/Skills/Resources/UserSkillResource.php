<?php

namespace App\Domain\Skills\Resources;

use App\Domain\Skills\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var UserSkill $this->resource */
        return [
            'id'         => $this->id,
            'userId'     => $this->user_id,
            'skillId'    => $this->skill_id,
            'level'      => $this->level,
            'acquiredAt' => $this->acquired_at?->toIso8601String(),
            'createdAt'  => $this->created_at?->toIso8601String(),
            'updatedAt'  => $this->updated_at?->toIso8601String(),
            'skill'      => $this->whenLoaded('skill', fn () => new SkillResource($this->skill)),
        ];
    }
}
