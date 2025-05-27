<?php

namespace App\Domain\Common\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                $q->orWhereRaw('LOWER(' . $this->wrapColumn($column) . ') LIKE ?', ['%' . strtolower($value) . '%']);
            }
        });
    }

    protected function wrapColumn(string $column): string
    {
        return DB::getQueryGrammar()->wrap(Str::snake($column));
    }
}
