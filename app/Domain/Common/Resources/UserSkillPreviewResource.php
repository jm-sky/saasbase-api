<?php

namespace App\Domain\Common\Resources;

use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Skill
 *
 * @property UserSkill $pivot
 */
class UserSkillPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->pivot->id,
            'name'       => $this->name,
            'level'      => $this->pivot->level,
            'acquiredAt' => $this->pivot->acquired_at?->toIso8601String(),
        ];
    }
}
