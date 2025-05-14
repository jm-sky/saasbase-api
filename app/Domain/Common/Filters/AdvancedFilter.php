<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class AdvancedFilter
 *
 * Supported filter operators:
 *
 * Comparison:
 *   - eq: Equal ( = )
 *   - ne, neq: Not equal ( != )
 *   - gt: Greater than ( > )
 *   - gte: Greater than or equal ( >= )
 *   - lt: Less than ( < )
 *   - lte: Less than or equal ( <= )
 *
 * String:
 *   - like: Contains (LIKE %value%)
 *   - nlike, notlike: Not contains (NOT LIKE %value%)
 *   - startswith: Starts with (LIKE value%)
 *   - endswith: Ends with (LIKE %value)
 *   - regex: Regular expression (REGEXP)
 *
 * Null checks:
 *   - null: Is NULL
 *   - notnull: Is NOT NULL
 *   - nullish: Is NULL or empty string
 *
 * Set membership:
 *   - in: In array/list
 *   - nin, notin: Not in array/list
 *   - between: Between two values (comma-separated or array)
 *
 * Usage:
 *   Pass an associative array of operators and values as the filter value.
 *   Example: ['gt' => 5, 'lt' => 10]
 */
class AdvancedFilter implements Filter
{
    protected array $columnTypes;

    public function __construct(array $columnTypes = [])
    {
        $this->columnTypes = $columnTypes;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if (is_array($value)) {
            foreach ($value as $operator => $val) {
                $this->applyOperator($query, $property, $operator, $val);
            }
        } else {
            $type = $this->columnTypes[$property] ?? 'string';

            if ('string' === $type) {
                $query->where($property, 'like', "%{$value}%");
            } else {
                $query->where($property, '=', $value);
            }
        }

        return $query;
    }

    protected function applyOperator(Builder $query, string $column, string $operator, $value): void
    {
        $op = strtolower($operator);

        match ($op) {
            'null'      => $query->whereNull($column),
            'notnull'   => $query->whereNotNull($column),
            'nullish'   => $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '')),
            'in'        => $query->whereIn($column, $this->splitToArray($value)),
            'nin', 'notin' => $query->whereNotIn($column, $this->splitToArray($value)),
            'between'   => $this->applyBetween($query, $column, $value),
            'like'      => $query->where($column, 'like', "%{$value}%"),
            'nlike', 'notlike' => $query->where($column, 'not like', "%{$value}%"),
            'startswith' => $query->where($column, 'like', "{$value}%"),
            'endswith'   => $query->where($column, 'like', "%{$value}"),
            'regex'      => $query->whereRaw("{$column} REGEXP ?", [$value]),
            default      => $query->where($column, $this->mapOperator($op), $value),
        };
    }

    protected function mapOperator(string $op): string
    {
        return match ($op) {
            'eq'  => '=',
            'ne', 'neq' => '!=',
            'gt'    => '>',
            'gte'   => '>=',
            'lt'    => '<',
            'lte'   => '<=',
            default => '=',
        };
    }

    protected function applyBetween(Builder $query, string $column, $value): void
    {
        $parts = $this->splitToArray($value);

        if (2 === count($parts)) {
            $query->whereBetween($column, [$parts[0], $parts[1]]);
        }
    }

    protected function splitToArray(string|array $value): array
    {
        return is_array($value) ? $value : explode(',', $value);
    }
}
