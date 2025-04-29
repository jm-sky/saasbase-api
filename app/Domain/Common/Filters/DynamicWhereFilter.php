<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Support\Arr;

class DynamicWhereFilter implements Filter
{
    protected array $columnTypes;

    public function __construct(array $columnTypes = [])
    {
        $this->columnTypes = $columnTypes;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        // If value is an array, operator is defined, e.g. ['gt' => 100]
        if (is_array($value)) {
            foreach ($value as $operator => $val) {
                $query->where($property, $this->mapOperator($operator), $val);
            }
        } else {
            // Use default operator depending on column type
            $type = $this->columnTypes[$property] ?? 'string';

            if ($type === 'string') {
                $query->where($property, 'like', "%{$value}%");
            } else {
                $query->where($property, '=', $value);
            }
        }

        return $query;
    }

    protected function mapOperator(string $op): string
    {
        return match ($op) {
            'eq' => '=',
            'neq', 'ne' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'like' => 'like',
            default => '='
        };
    }
}
