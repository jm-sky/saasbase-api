<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Financial\Models\PKWiUClassification;

final class PKWiUClassificationDTO
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $parentCode,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $level,
        public readonly bool $isActive,
        public readonly ?array $children = null,
        public readonly ?string $hierarchyPath = null
    ) {
    }

    public static function fromModel(PKWiUClassification $classification): self
    {
        return new self(
            code: $classification->code,
            parentCode: $classification->parent_code,
            name: $classification->name,
            description: $classification->description,
            level: $classification->level,
            isActive: $classification->is_active,
            hierarchyPath: $classification->getFullHierarchyPath()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            parentCode: $data['parent_code'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            level: $data['level'],
            isActive: $data['is_active'] ?? true,
            children: $data['children'] ?? null,
            hierarchyPath: $data['hierarchy_path'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'code'           => $this->code,
            'parent_code'    => $this->parentCode,
            'name'           => $this->name,
            'description'    => $this->description,
            'level'          => $this->level,
            'is_active'      => $this->isActive,
            'children'       => $this->children,
            'hierarchy_path' => $this->hierarchyPath,
        ];
    }
}
