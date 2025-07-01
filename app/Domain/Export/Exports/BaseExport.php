<?php

namespace App\Domain\Export\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Abstract base class for Excel exports.
 *
 * @property array $filters
 * @property array $columns
 * @property array $formatting
 * @property array $dateColumns
 * @property array $dateTimeColumns
 * @property array $currencyColumns
 * @property array $amountColumns
 */
abstract class BaseExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters = [];

    protected array $columns = [];

    protected array $formatting = [];

    protected array $dateColumns = [];

    protected array $dateTimeColumns = [];

    protected array $currencyColumns = [];

    protected array $amountColumns = [];

    public function __construct(array $filters = [], array $columns = [], array $formatting = [])
    {
        $this->filters    = $filters;
        $this->columns    = !empty($columns) ? $columns : $this->columns;
        $this->formatting = $formatting;
    }

    /**
     * Return the base query for the export.
     */
    abstract public function baseQuery(): Builder;

    /**
     * Return the query with filters and includes applied.
     */
    public function query(): Builder
    {
        return QueryBuilder::for($this->baseQuery())
            ->allowedFilters($this->allowedFilters())
            ->allowedIncludes($this->allowedIncludes())
            ->getEloquentBuilder()
        ;
    }

    /**
     * Define allowed filters for the export.
     */
    protected function allowedFilters(): array
    {
        return [];
    }

    /**
     * Define allowed includes for the export.
     */
    protected function allowedIncludes(): array
    {
        return [];
    }

    /**
     * Format a column name into a readable heading.
     */
    protected function formatColumnName(string $column): string
    {
        // Split the column by dots to handle nested relationships
        $parts = explode('.', $column);

        // Convert each part to a readable format
        $parts = array_map(function ($part) {
            // Convert snake_case to words
            $part = str_replace('_', ' ', $part);
            // Convert camelCase to words
            $part = preg_replace('/(?<!^)[A-Z]/', ' $0', $part);

            // Capitalize first letter of each word
            return ucwords($part);
        }, $parts);

        // Join the parts with a meaningful separator
        return implode(' - ', $parts);
    }

    /**
     * Return the headings for the export.
     */
    public function headings(): array
    {
        return collect($this->columns)
            ->map(fn ($col) => $this->formatColumnName($col))
            ->toArray()
        ;
    }

    /**
     * Map a row for the export, applying formatting.
     */
    public function map($row): array
    {
        return collect($this->columns)->map(function ($col) use ($row) {
            $value = data_get($row, $col);

            if (in_array($col, $this->dateColumns)) {
                return optional(\Carbon\Carbon::parse($value))->format($this->formatting['date'] ?? 'Y-m-d');
            }

            if (in_array($col, $this->dateTimeColumns)) {
                return optional(\Carbon\Carbon::parse($value))->format($this->formatting['datetime'] ?? 'Y-m-d H:i');
            }

            if (in_array($col, $this->currencyColumns)) {
                return number_format((float) $value, 2, ',', ' ') . ' ' . ($this->formatting['currency'] ?? 'PLN');
            }

            if (in_array($col, $this->amountColumns)) {
                return number_format((float) $value, 2, ',', ' ');
            }

            // Placeholder for custom column transformers
            // if (isset($this->columnTransformers[$col])) {
            //     return call_user_func($this->columnTransformers[$col], $value, $row);
            // }

            // Placeholder for column merging logic
            // if (isset($this->columnMergers[$col])) {
            //     return call_user_func($this->columnMergers[$col], $row);
            // }

            return $value;
        })->toArray();
    }

    /**
     * Style the worksheet (e.g., bold header row).
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
