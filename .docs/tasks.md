# Tasks

## 1. [x] Add middleware to set locale based on Accept Language header. 

```
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        // Pobierz listę z configu
        $supportedLocales = Config::get('app.supported_locales', ['en']);

        // Laravelowy helper do analizy Accept-Language
        $locale = $request->getPreferredLanguage($supportedLocales);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
```
-----------------------------------------------------

## 2. [ ] Add seeders for all models. I need seeder for skill categories and skills, lets start with IT area. I want to have a demo with few users, few tanants, some contractors, products, projects & tasks.

## 3. [ ] Add countries to Country seeders JSON file (European countries, and most large countries)

## 4. [ ] Add routes & actions for current user - change settings, reset password etc. 

## 5. [ ] Add trait (BelongsToTenant?) tht apply global scope for models with tenant_id. We'll store tenant_id in session or jwt. Its for security.

## 6. [ ] Refactor foreign keys. i.e. refer country code (pl, de) instead of id. Analyse.

---

## 7. [ ] Implement standardized filtering and sorting with Spatie Query Builder to index method in all CRUD controllers. 

Create trait 
```
// app/Http/Controllers/Concerns/HasIndexQuery.php

namespace App\Http\Controllers\Concerns;

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
    public function getIndexQuery(Request $request): Builder
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
```

Create DateRangeFilter
```
namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter implements Filter
{
    protected string $column;

    public function __construct(string $column = 'date')
    {
        $this->column = $column;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $dates = explode(',', $value);
        $from = $dates[0] ?? null;
        $to = $dates[1] ?? null;

        if ($from && !$to) {
            $query->whereDate($this->column, '=', $from);
        } elseif ($from && $to) {
            $query->whereDate($this->column, '>=', $from)
                  ->whereDate($this->column, '<=', $to);
        }
    }
}
```

Use this trait in controllers, fill `$filters` wilth all columns of domain model, and some relations if needed. 

Create sth like `Search[model]Request` with validation for those filters.

Add tests. 

---
