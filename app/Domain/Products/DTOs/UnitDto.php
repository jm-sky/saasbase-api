<?php

namespace App\Domain\Products\DTOs;

use Carbon\Carbon;

class UnitDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
    ) {}

    public static function fromModel(\App\Domain\Common\Models\Unit $model): self
    {
        return new self(
            id: $model->id,
            code: $model->code,
            name: $model->name,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
