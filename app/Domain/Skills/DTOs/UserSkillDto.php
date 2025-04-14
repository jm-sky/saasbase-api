<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use Carbon\Carbon;

class UserSkillDto
{
    public function __construct(
        public readonly string $user_id,
        public readonly string $skill_id,
        public readonly int $level,
        public readonly ?Carbon $acquired_at,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $user = null,
        public readonly ?SkillDto $skill = null,
    ) {}

    public static function fromModel(\App\Domain\Skills\Models\UserSkill $model, bool $withRelations = false): self
    {
        return new self(
            user_id: $model->user_id,
            skill_id: $model->skill_id,
            level: $model->level,
            acquired_at: $model->acquired_at,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            user: $withRelations && $model->relationLoaded('user')
                ? UserDto::fromModel($model->user)
                : null,
            skill: $withRelations && $model->relationLoaded('skill')
                ? SkillDto::fromModel($model->skill)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'skill_id' => $this->skill_id,
            'level' => $this->level,
            'acquired_at' => $this->acquired_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'user' => $this->user?->toArray(),
            'skill' => $this->skill?->toArray(),
        ];
    }
}
