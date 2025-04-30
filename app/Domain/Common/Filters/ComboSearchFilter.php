<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ComboSearchFilter implements Filter
{
    protected array $columns;

    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($q) use ($value) {
            foreach ($this->columns as $column) {
                $q->orWhere($column, 'like', "%{$value}%");
            }
        });
    }
}
