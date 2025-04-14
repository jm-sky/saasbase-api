<?php

namespace App\Domain\Projects\DTOs;

use Carbon\Carbon;

class ProjectRoleDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenant_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?array $permissions,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\ProjectRole $model): self
    {
        return new self(
            id: $model->id,
            tenant_id: $model->tenant_id,
            name: $model->name,
            description: $model->description,
            permissions: $model->permissions,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
