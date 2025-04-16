<?php

namespace App\Domain\Skills\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $categoryId
 * @property string $name
 * @property ?string $description
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class SkillDTO extends Data
{
    public function __construct(
        public readonly string $categoryId,
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
