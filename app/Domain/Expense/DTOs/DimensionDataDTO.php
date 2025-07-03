<?php

namespace App\Domain\Expense\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Expense\Contracts\AllocationDimensionInterface;
use App\Domain\Expense\Enums\AllocationDimensionType;

/**
 * @property AllocationDimensionType        $dimensionType
 * @property string                         $label
 * @property string                         $labelEN
 * @property bool                           $isAlwaysVisible
 * @property bool                           $isConfigurable
 * @property AllocationDimensionInterface[] $items
 */
final class DimensionDataDTO extends BaseDataDTO
{
    /**
     * @param AllocationDimensionInterface[] $items Array of dimension items (models implementing AllocationDimensionInterface)
     */
    public function __construct(
        public readonly AllocationDimensionType $dimensionType,
        public readonly string $label,
        public readonly string $labelEN,
        public readonly bool $isAlwaysVisible,
        public readonly bool $isConfigurable,
        public readonly array $items,
    ) {
    }

    public function toArray(): array
    {
        return [
            'dimensionType'   => $this->dimensionType->value,
            'label'           => $this->label,
            'labelEN'         => $this->labelEN,
            'isAlwaysVisible' => $this->isAlwaysVisible,
            'isConfigurable'  => $this->isConfigurable,
            'items'           => $this->items, // Items will be transformed to array by resource
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            dimensionType: AllocationDimensionType::from($data['dimensionType']),
            label: $data['label'],
            labelEN: $data['labelEN'],
            isAlwaysVisible: $data['isAlwaysVisible'],
            isConfigurable: $data['isConfigurable'],
            items: $data['items'] ?? [],
        );
    }

    /**
     * Create DTO from AllocationDimensionType with items.
     *
     * @param AllocationDimensionInterface[] $items
     */
    public static function fromDimensionTypeWithItems(
        AllocationDimensionType $dimensionType,
        array $items
    ): static {
        return new self(
            dimensionType: $dimensionType,
            label: $dimensionType->label(),
            labelEN: $dimensionType->labelEN(),
            isAlwaysVisible: $dimensionType->isAlwaysVisible(),
            isConfigurable: $dimensionType->isConfigurable(),
            items: $items,
        );
    }
}
