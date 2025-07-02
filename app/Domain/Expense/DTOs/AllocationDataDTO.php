<?php

namespace App\Domain\Expense\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Brick\Math\BigDecimal;

/**
 * @property BigDecimal               $amount
 * @property ?string                  $note
 * @property AllocationDimensionDTO[] $dimensions
 */
final class AllocationDataDTO extends BaseDataDTO
{
    /**
     * @param AllocationDimensionDTO[] $dimensions
     */
    public function __construct(
        public readonly BigDecimal $amount,
        public readonly ?string $note,
        public readonly array $dimensions = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'amount'     => $this->amount->toFloat(),
            'note'       => $this->note,
            'dimensions' => array_map(fn (AllocationDimensionDTO $dimension) => $dimension->toArray(), $this->dimensions),
        ];
    }

    public static function fromArray(array $data): static
    {
        $dimensions = [];

        if (isset($data['dimensions']) && is_array($data['dimensions'])) {
            $dimensions = array_map(
                fn (array $dimensionData) => AllocationDimensionDTO::fromArray($dimensionData),
                $data['dimensions']
            );
        }

        return new self(
            amount: BigDecimal::of($data['amount']),
            note: $data['note'] ?? null,
            dimensions: $dimensions,
        );
    }

    /**
     * Create collection of AllocationDataDTO from array.
     *
     * @return AllocationDataDTO[]
     */
    public static function collectFromArray(array $data): array
    {
        return array_map(
            fn (array $allocationData) => self::fromArray($allocationData),
            $data
        );
    }
}
