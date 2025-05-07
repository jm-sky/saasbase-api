<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Projects\Models\ProjectRequiredSkill;
use App\Domain\Skills\DTOs\SkillDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<ProjectRequiredSkill>
 *
 * @property ?string   $id            UUID
 * @property string    $projectId
 * @property string    $skillId
 * @property int       $requiredLevel
 * @property ?Carbon   $createdAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $updatedAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $deletedAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?SkillDTO $skill
 */
class ProjectRequiredSkillDTO extends BaseDTO
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $skillId,
        public readonly int $requiredLevel,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?SkillDTO $skill = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var ProjectRequiredSkill $model */
        return new static(
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

    public static function fromArray(array $data): static
    {
        return new static(
            projectId: $data['project_id'],
            skillId: $data['skill_id'],
            requiredLevel: $data['required_level'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            skill: isset($data['skill']) ? SkillDTO::fromArray($data['skill']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'projectId'     => $this->projectId,
            'skillId'       => $this->skillId,
            'requiredLevel' => $this->requiredLevel,
            'createdAt'     => $this->createdAt?->toIso8601String(),
            'updatedAt'     => $this->updatedAt?->toIso8601String(),
            'deletedAt'     => $this->deletedAt?->toIso8601String(),
            'skill'         => $this->skill?->toArray(),
        ];
    }
}
