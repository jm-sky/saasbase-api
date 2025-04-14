<?php

namespace App\Domain\Products\DTOs;

use Carbon\Carbon;

class VatRateDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $rate,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
    ) {}

    public static function fromModel(\App\Domain\Common\Models\VatRate $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            rate: $model->rate,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
