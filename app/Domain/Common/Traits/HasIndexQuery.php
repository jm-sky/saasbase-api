<?php

namespace App\Domain\Common\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
        // @phpstan-ignore-next-line
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort)
            ->with($this->defaultWith)
            ->withoutGlobalScopes($this->withoutGlobalScopes)
            ->getEloquentBuilder()
        ;
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

    protected function getPaginatorPerPage(Request $request): int
    {
        return $request->input('perPage', $this->defaultPerPage);
    }

    protected function getPaginatorMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage'  => $paginator->currentPage(),
            'lastPage'     => $paginator->lastPage(),
            'perPage'      => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }
}
