<?php

namespace App\Domain\Common\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;

class JsonbPathSort implements Sort
{
    protected string $jsonbColumn;

    protected string $jsonPath; // e.g. 'address.street'

    public function __construct(string $jsonbColumn, string $jsonPath)
    {
        $this->jsonbColumn = $jsonbColumn;
        $this->jsonPath    = $jsonPath;
    }

    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';

        $path = "'{" . implode(',', explode('.', $this->jsonPath)) . "}'";
        $query->orderByRaw("{$this->jsonbColumn} #>> {$path} {$direction}");
    }
}
