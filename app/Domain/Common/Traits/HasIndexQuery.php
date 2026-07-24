<?php

namespace App\Domain\Common\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

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
     * Default number of items per page.
     */
    protected int $defaultPerPage = 15;

    /**
     * Default with option.
     */
    protected array $defaultWith = [];

    /**
     * Default without global scopes option.
     */
    protected array $withoutGlobalScopes = [];

    /**
     * Create the base query using Spatie QueryBuilder.
     */
    public function getIndexQuery(Request $request): Builder
    {
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters(...$this->filters)
            ->allowedSorts(...$this->normalizeSorts($this->sorts))
            ->defaultSort($this->defaultSort)
            ->with($this->defaultWith)
            ->withoutGlobalScopes($this->withoutGlobalScopes)
            // @phpstan-ignore-next-line getEloquentBuilder() is declared on Spatie's QueryBuilder, not visible to PHPStan once the chain returns the base Eloquent Builder type
            ->getEloquentBuilder();
    }

    /**
     * Return paginated results with metadata.
     */
    public function getIndexPaginator(Request $request, ?int $perPage = null, ?Builder $query = null): array
    {
        $query ??= $this->getIndexQuery($request);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage ?? $this->getPaginatorPerPage($request));

        return [
            'data' => $paginator->items(),
            'meta' => $this->getPaginatorMeta($paginator),
        ];
    }

    /**
     * Spatie's AllowedSort::field($apiName, $columnName) is the supported way to alias
     * a sort key. Historically this trait was fed a raw ['apiName' => 'column_name', ...]
     * array mixed with plain (numeric-keyed) column names and handed straight to
     * QueryBuilder::allowedSorts(array $sorts). Since allowedSorts() became variadic,
     * spreading a mixed int/string-keyed array directly is unsafe (PHP forbids a
     * positional item after a named one during unpacking whenever a string key isn't
     * trailing) — so aliases are normalized into AllowedSort instances here instead.
     *
     * @param  array<int|string, AllowedSort|string>  $sorts
     * @return array<int, AllowedSort|string>
     */
    protected function normalizeSorts(array $sorts): array
    {
        $normalized = [];

        foreach ($sorts as $key => $value) {
            $normalized[] = is_string($key) ? AllowedSort::field($key, $value) : $value;
        }

        return $normalized;
    }

    protected function getPaginatorPerPage(Request $request): int
    {
        return $request->input('perPage', $this->defaultPerPage);
    }

    protected function getPaginatorMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
