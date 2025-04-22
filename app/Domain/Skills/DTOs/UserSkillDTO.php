<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Skills\Models\UserSkill;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id         UUID
 * @property string  $userId
 * @property string  $skillId
 * @property int     $level
 * @property string  $acquiredAt
 * @property ?Carbon $createdAt  Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt  Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt  Internally Carbon, accepts/serializes ISO 8601
 */
class UserSkillDTO extends Data
{
    public function __construct(
        public readonly string $userId,
        public readonly string $skillId,
        public readonly int $level,
        public readonly ?string $acquiredAt,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(UserSkill $model): self
    {
        return new self(
            userId: $model->user_id,
            skillId: $model->skill_id,
            level: $model->level,
            acquiredAt: $model->acquired_at?->format('Y-m-d'),
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
