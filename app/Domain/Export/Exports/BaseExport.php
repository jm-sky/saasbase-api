<?php

namespace App\Domain\Export\Exports;

use Carbon\Carbon;
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
abstract class BaseExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $this->filters = $filters;
        $this->columns = $this->resolveColumns($columns);
        $this->formatting = $formatting;
    }

    /**
     * The client may only pick a subset/order of the subclass's own
     * $columns whitelist — never arbitrary column/relation paths.
     * Without this, a caller could pass e.g. columns[]=assignee.password
     * and get it back verbatim: data_get() walks relations regardless of
     * allowedIncludes(), and reads properties directly (bypassing
     * Eloquent's $hidden, which is only enforced by toArray()/toJson()).
     */
    private function resolveColumns(array $requested): array
    {
        if (empty($requested)) {
            return $this->columns;
        }

        $allowed = array_values(array_intersect($requested, $this->columns));

        return ! empty($allowed) ? $allowed : $this->columns;
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
            ->allowedFilters(...$this->allowedFilters())
            ->allowedIncludes(...$this->allowedIncludes())
            ->getEloquentBuilder();
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
            ->toArray();
    }

    /**
     * Map a row for the export, applying formatting.
     */
    public function map($row): array
    {
        return collect($this->columns)->map(function ($col) use ($row) {
            $value = data_get($row, $col);

            if (in_array($col, $this->dateColumns)) {
                return optional(Carbon::parse($value))->format($this->formatting['date'] ?? 'Y-m-d');
            }

            if (in_array($col, $this->dateTimeColumns)) {
                return optional(Carbon::parse($value))->format($this->formatting['datetime'] ?? 'Y-m-d H:i');
            }

            if (in_array($col, $this->currencyColumns)) {
                return number_format((float) $value, 2, ',', ' ').' '.($this->formatting['currency'] ?? 'PLN');
            }

            if (in_array($col, $this->amountColumns)) {
                return number_format((float) $value, 2, ',', ' ');
            }

            return $this->neutralizeFormula($value);
        })->toArray();
    }

    /**
     * CSV/Excel formula injection (CWE-1236): a value starting with
     * =/+/-/@ is parsed by Excel as a live formula when the cell is
     * opened, not as literal text. Any free-text field (contractor name,
     * task title, ...) can carry this from data entry straight into an
     * exported .xlsx. Prefixing with a single quote forces text
     * interpretation, matching the standard mitigation for this class.
     */
    private function neutralizeFormula(mixed $value): mixed
    {
        if (! \is_string($value) || $value === '') {
            return $value;
        }

        if (\in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
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
