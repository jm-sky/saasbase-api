<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Projects\Models\ProjectRequiredSkill;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $projectId
 * @property string $skillId
 * @property int $requiredLevel
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?SkillDTO $skill
 */
class ProjectRequiredSkillDTO extends Data
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $skillId,
        public readonly int $requiredLevel,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
        public ?SkillDTO $skill = null,
    ) {}

    public static function fromModel(ProjectRequiredSkill $model): self
    {
        return new self(
            projectId: $model->project_id,
            skillId: $model->skill_id,
            requiredLevel: $model->required_level,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            skill: $model->skill ? SkillDTO::fromModel($model->skill) : null,
        );
    }
}
