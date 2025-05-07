<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Skills\Models\UserSkill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<UserSkill>
 *
 * @property ?string   $id         UUID
 * @property string    $userId     UUID
 * @property string    $skillId    UUID
 * @property int       $level      1-5
 * @property ?Carbon   $acquiredAt When the skill was acquired, Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $createdAt  Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $updatedAt  Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $deletedAt  Internally Carbon, accepts/serializes ISO 8601
 * @property ?SkillDTO $skill
 */
class UserSkillDTO extends BaseDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $skillId,
        public readonly int $level,
        public readonly ?string $id = null,
        public readonly ?Carbon $acquiredAt = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?SkillDTO $skill = null,
    ) {
        if ($level < 1 || $level > 5) {
            throw new \InvalidArgumentException('Skill level must be between 1 and 5');
        }
    }

    public static function fromModel(Model $model): static
    {
        /* @var UserSkill $model */
        return new static(
            userId: $model->user_id,
            skillId: $model->skill_id,
            level: $model->level,
            id: $model->id,
            acquiredAt: $model->acquired_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            skill: $model->skill ? SkillDTO::fromModel($model->skill) : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['user_id'],
            skillId: $data['skill_id'],
            level: $data['level'],
            id: $data['id'] ?? null,
            acquiredAt: isset($data['acquired_at']) ? Carbon::parse($data['acquired_at']) : null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            skill: isset($data['skill']) ? SkillDTO::fromArray($data['skill']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'userId'      => $this->userId,
            'skillId'     => $this->skillId,
            'level'       => $this->level,
            'acquiredAt'  => $this->acquiredAt?->toDateString(),
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
            'skill'       => $this->skill?->toArray(),
        ];
    }
}
