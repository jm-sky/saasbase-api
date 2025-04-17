<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Projects\Models\ProjectRequiredSkill;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $projectId
 * @property string $skillId
 * @property int $requiredLevel
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?SkillDTO $skill
 */
class ProjectRequiredSkillDTO extends Data
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $skillId,
        public readonly int $requiredLevel,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?SkillDTO $skill = null,
    ) {}

    public static function fromModel(ProjectRequiredSkill $model): self
    {
        return new self(
            projectId: $model->project_id,
            skillId: $model->skill_id,
            requiredLevel: $model->required_level,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
            skill: $model->skill ? SkillDTO::fromModel($model->skill) : null,
        );
    }
}
