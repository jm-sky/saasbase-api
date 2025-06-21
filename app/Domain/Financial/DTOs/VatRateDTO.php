<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\Enums\VatRateType;
use App\Domain\Common\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

final class VatRateDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public float $rate,
        public VatRateType $type,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var VatRate $model */
        return new self(
            id: $model->id,
            name: $model->name,
            rate: $model->rate,
            type: $model->type,
        );
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type->value,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            rate: $data['rate'],
            type: VatRateType::from($data['type']),
        );
    }
}
