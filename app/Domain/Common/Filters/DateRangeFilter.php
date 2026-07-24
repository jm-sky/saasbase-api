<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DateRangeFilter implements Filter
{
    public function __construct(
        private readonly string $field
    ) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_array($value) || ! isset($value['from']) || ! isset($value['to'])) {
            return;
        }

        $query->whereBetween($this->field, [$value['from'], $value['to']]);
    }
}
