<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DynamicWhereFilter implements Filter
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
        $mapped = $this->mapOperator($operator);

        match ($mapped) {
            'in'       => $query->whereIn($column, $this->splitToArray($value)),
            'not in'   => $query->whereNotIn($column, $this->splitToArray($value)),
            'like'     => $query->where($column, 'like', "%{$value}%"),
            'not like' => $query->where($column, 'not like', "%{$value}%"),
            'between'  => $this->applyBetween($query, $column, $value),
            default    => $query->where($column, $mapped, $value),
        };
    }

    protected function mapOperator(string $op): string
    {
        return match (strtolower($op)) {
            'eq' => '=',
            'ne', 'neq' => '!=',
            'gt'  => '>',
            'gte' => '>=',
            'lt'  => '<',
            'lte' => '<=',
            'in'  => 'in',
            'nin', 'notin' => 'not in',
            'like' => 'like',
            'nlike', 'notlike' => 'not like',
            'between' => 'between',
            default   => '=',
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
        if (is_array($value)) {
            return $value;
        }

        return explode(',', $value);
    }
} App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class CustomWhereFilter implements Filter
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
        $mapped = $this->mapOperator($operator);

        match ($mapped) {
            'in'       => $query->whereIn($column, $this->splitToArray($value)),
            'not in'   => $query->whereNotIn($column, $this->splitToArray($value)),
            'like'     => $query->where($column, 'like', "%{$value}%"),
            'not like' => $query->where($column, 'not like', "%{$value}%"),
            'between'  => $this->applyBetween($query, $column, $value),
            default    => $query->where($column, $mapped, $value),
        };
    }

    protected function mapOperator(string $op): string
    {
        return match (strtolower($op)) {
            'eq' => '=',
            'ne', 'neq' => '!=',
            'gt'  => '>',
            'gte' => '>=',
            'lt'  => '<',
            'lte' => '<=',
            'in'  => 'in',
            'nin', 'notin' => 'not in',
            'like' => 'like',
            'nlike', 'notlike' => 'not like',
            'between' => 'between',
            default   => '=',
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
        if (is_array($value)) {
            return $value;
        }

        return explode(',', $value);
    }
}
