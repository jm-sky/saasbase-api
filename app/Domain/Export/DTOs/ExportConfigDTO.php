<?php

namespace App\Domain\Export\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Data Transfer Object for Excel export configuration.
 *
 * @property array $filters    Filters to apply to the export query
 * @property array $columns    Columns to include in the export
 * @property array $formatting Formatting options (date, datetime, currency)
 */
class ExportConfigDTO extends BaseDataDTO
{
    public function __construct(
        public readonly array $filters = [],
        public readonly array $columns = [],
        public readonly array $formatting = [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i',
            'currency' => 'PLN',
        ]
    ) {
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
