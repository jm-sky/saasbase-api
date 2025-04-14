<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Skills\DTOs\SkillDto;
use Carbon\Carbon;

class ProjectRequiredSkillDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $project_id,
        public readonly string $skill_id,
        public readonly int $required_level,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?SkillDto $skill = null,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\ProjectRequiredSkill $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            project_id: $model->project_id,
            skill_id: $model->skill_id,
            required_level: $model->required_level,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            skill: $withRelations && $model->relationLoaded('skill')
                ? SkillDto::fromModel($model->skill)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'skill_id' => $this->skill_id,
            'required_level' => $this->required_level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'skill' => $this->skill?->toArray(),
        ];
    }
}
