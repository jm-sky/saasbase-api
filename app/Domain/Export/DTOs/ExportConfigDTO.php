<?php

namespace App\Domain\Export\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Exchanges\Models\Currency;

/**
 * Data Transfer Object for Excel export configuration.
 *
 * @property array $filters    Filters to apply to the export query
 * @property array $columns    Columns to include in the export
 * @property array $formatting Formatting options (date, datetime, currency)
 */
final class ExportConfigDTO extends BaseDataDTO
{
    public function __construct(
        public readonly array $filters = [],
        public readonly array $columns = [],
        public readonly array $formatting = [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i',
            'currency' => Currency::POLISH_CURRENCY_CODE,
        ]
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            filters: $data['filters'],
            columns: $data['columns'],
            formatting: $data['formatting'],
        );
    }

    public function toArray(): array
    {
        return [
            'filters'    => $this->filters,
            'columns'    => $this->columns,
            'formatting' => $this->formatting,
        ];
    }
}
