<?php

namespace App\Domain\Expense\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Expense\Enums\AllocationDimensionType;

/**
 * @property AllocationDimensionType $type
 * @property string                  $id
 */
final class AllocationDimensionDTO extends BaseDataDTO
{
    public function __construct(
        public readonly AllocationDimensionType $type,
        public readonly string $id,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'id'   => $this->id,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            type: AllocationDimensionType::from($data['type']),
            id: $data['id'],
        );
    }
}
