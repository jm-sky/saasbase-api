<?php

namespace App\Domain\Skills\DTO;

use Spatie\LaravelData\Data;

/**
 * @property string $userId
 * @property string $skillId
 * @property int $level
 * @property ?string $acquiredAt
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class UserSkillDTO extends Data
{
    public function __construct(
        public string $userId,
        public string $skillId,
        public int $level,
        public ?string $acquiredAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
