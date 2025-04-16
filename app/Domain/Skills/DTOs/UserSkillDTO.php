<?php

namespace App\Domain\Skills\DTOs;

use Spatie\LaravelData\Data;
use App\Domain\Skills\Models\UserSkill;

/**
 * @property ?string $id UUID
 * @property string $userId
 * @property string $skillId
 * @property int $level
 * @property string $acquiredAt
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class UserSkillDTO extends Data
{
    public function __construct(
        public readonly string $userId,
        public readonly string $skillId,
        public readonly int $level,
        public readonly ?string $acquiredAt,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(UserSkill $model): self
    {
        return new self(
            userId: $model->user_id,
            skillId: $model->skill_id,
            level: $model->level,
            acquiredAt: $model->acquired_at?->format('Y-m-d'),
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
