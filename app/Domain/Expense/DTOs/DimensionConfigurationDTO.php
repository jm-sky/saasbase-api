<?php

namespace App\Domain\Expense\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Expense\Enums\AllocationDimensionType;

/**
 * @property AllocationDimensionType $dimensionType
 * @property string                  $label
 * @property string                  $labelEN
 * @property bool                    $isEnabled
 * @property bool                    $isAlwaysVisible
 * @property bool                    $isConfigurable
 * @property int                     $displayOrder
 * @property int                     $defaultDisplayOrder
 */
final class DimensionConfigurationDTO extends BaseDataDTO
{
    public function __construct(
        public readonly AllocationDimensionType $dimensionType,
        public readonly string $label,
        public readonly string $labelEN,
        public readonly bool $isEnabled,
        public readonly bool $isAlwaysVisible,
        public readonly bool $isConfigurable,
        public readonly int $displayOrder,
        public readonly int $defaultDisplayOrder,
    ) {
    }

    public function toArray(): array
    {
        return [
            'dimensionType'        => $this->dimensionType->value,
            'label'                => $this->label,
            'labelEN'              => $this->labelEN,
            'isEnabled'            => $this->isEnabled,
            'isAlwaysVisible'      => $this->isAlwaysVisible,
            'isConfigurable'       => $this->isConfigurable,
            'displayOrder'         => $this->displayOrder,
            'defaultDisplayOrder'  => $this->defaultDisplayOrder,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            dimensionType: AllocationDimensionType::from($data['dimensionType']),
            label: $data['label'],
            labelEN: $data['labelEN'],
            isEnabled: $data['isEnabled'],
            isAlwaysVisible: $data['isAlwaysVisible'],
            isConfigurable: $data['isConfigurable'],
            displayOrder: $data['displayOrder'],
            defaultDisplayOrder: $data['defaultDisplayOrder'],
        );
    }

    /**
     * Create DTO from AllocationDimensionType with configuration data.
     */
    public static function fromDimensionType(
        AllocationDimensionType $dimensionType,
        bool $isEnabled = false,
        int $displayOrder = 0
    ): static {
        return new self(
            dimensionType: $dimensionType,
            label: $dimensionType->label(),
            labelEN: $dimensionType->labelEN(),
            isEnabled: $isEnabled,
            isAlwaysVisible: $dimensionType->isAlwaysVisible(),
            isConfigurable: $dimensionType->isConfigurable(),
            displayOrder: $displayOrder,
            defaultDisplayOrder: $dimensionType->getDefaultDisplayOrder(),
        );
    }
}
