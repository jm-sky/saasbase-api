<?php

namespace App\Domain\Common\Concerns;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

trait HasIndexQuery
{
    /**
     * The model class to query.
     */
    protected string $modelClass;

    /**
     * Allowed filters for the query.
     */
    protected array $filters = [];

    /**
     * Allowed sorts for the query.
     */
    protected array $sorts = [];

    /**
     * Default sort option.
     */
    protected string $defaultSort = '-id';

    /**
     * Create the base query using Spatie QueryBuilder.
     */
    public function getIndexQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort);
    }

    /**
     * Return paginated results.
     */
    public function getIndexPaginator(Request $request): LengthAwarePaginator
    {
        return $this->getIndexQuery($request)->paginate()->appends($request->query());
    }
}
