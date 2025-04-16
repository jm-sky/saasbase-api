<?php

namespace App\Domain\Skills\DTO;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property ?string $description
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class SkillCategoryDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
