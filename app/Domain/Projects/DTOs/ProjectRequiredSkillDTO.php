<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Skills\DTOs\SkillDTO;
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
}
