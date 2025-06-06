<?php

namespace App\Domain\Common\Filters;

use App\Domain\Common\Enums\AdvancedFilterOperator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class AdvancedFilter.
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
            AdvancedFilterOperator::IsNull->value      => $query->whereNull($column),
            AdvancedFilterOperator::IsNotNull->value   => $query->whereNotNull($column),
            AdvancedFilterOperator::IsNullish->value   => $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '')),
            AdvancedFilterOperator::In->value          => $query->whereIn($column, $this->splitToArray($value)),
            AdvancedFilterOperator::NotIn->value, AdvancedFilterOperator::NotInAlt->value => $query->whereNotIn($column, $this->splitToArray($value)),
            AdvancedFilterOperator::Between->value   => $this->applyBetween($query, $column, $value),
            AdvancedFilterOperator::Like->value      => $query->where($column, 'like', "%{$value}%"),
            AdvancedFilterOperator::NotLike->value, AdvancedFilterOperator::NotLikeAlt->value => $query->where($column, 'not like', "%{$value}%"),
            AdvancedFilterOperator::StartsWith->value => $query->where($column, 'like', "{$value}%"),
            AdvancedFilterOperator::EndsWith->value   => $query->where($column, 'like', "%{$value}"),
            AdvancedFilterOperator::Regex->value      => $query->whereRaw("{$column} REGEXP ?", [$value]),
            default                                   => $query->where($column, $this->mapOperator($op), $value),
        };
    }

    protected function mapOperator(string $op): string
    {
        return match ($op) {
            AdvancedFilterOperator::Equals->value  => '=',
            AdvancedFilterOperator::NotEquals->value, AdvancedFilterOperator::NotEqualsAlt->value => '!=',
            AdvancedFilterOperator::GreaterThan->value           => '>',
            AdvancedFilterOperator::GreaterThanOrEqual->value    => '>=',
            AdvancedFilterOperator::From->value                  => '>=',
            AdvancedFilterOperator::LessThan->value              => '<',
            AdvancedFilterOperator::LessThanOrEqual->value       => '<=',
            AdvancedFilterOperator::To->value                    => '<=',
            default                                              => '=',
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
