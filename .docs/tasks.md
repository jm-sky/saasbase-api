# Tasks

## 1. [x] Add middleware to set locale based on Accept Language header. 

```php
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

---

## 2. [ ] Add seeders for all models. I need seeder for skill categories and skills, let's start with IT area. I want to have a demo with few users, few tenants, some contractors, products, projects & tasks.

- **Subtasks**:
  - Create seeder for skill categories and skills (IT area).
  - Create seeder for users (demo users, include users with different roles).
  - Create seeder for tenants (few tenants).
  - Create seeder for contractors (few contractors).
  - Create seeder for products (demo products).
  - Create seeder for projects (few demo projects).
  - Create seeder for tasks (few tasks related to projects).

## 3. [ ] Add countries to Country seeders JSON file (European countries, and most large countries).
- **Note**: We can include all countries if performance is not impacted.
- **Subtasks**:
  - Review existing Country seeder.
  - Add all countries (Europe and large countries) to the seeder.
  - Test seeding functionality.

## 4. [ ] Add routes & actions for current user - change settings, reset password etc.
- **Suggested**: Use Actions instead of controller methods for better organization.
- **Subtasks**:
  - Create actions for changing user settings (username, email, etc.).
  - Create action for resetting password.
  - Create action for updating user profile.
  - Implement action for changing language preference.
  - Implement validation for user settings actions.

## 5. [ ] Add trait (BelongsToTenant?) that applies a global scope for models with tenant_id. We'll store tenant_id in session or JWT for security.  
- **Note**: User does not have tenant_id; a user can belong to many tenants, not just one.
- **Subtasks**:
  - Create a `BelongsToTenant` trait that applies a global scope for models.
  - Implement session or JWT storage for tenant identification.
  - Refactor models that should be tenant-scoped.
  - Add unit tests for tenant scoping.

## 6. [ ] Refactor foreign keys. i.e. refer to country code (pl, de) instead of id. Analyse.  
- **Note**: It may not be more efficient, but we would immediately see the country name instead of an anonymous ID.
- **Subtasks**:
  - Review foreign key usage for countries.
  - Replace country IDs with country codes (pl, de) in relevant models.
  - Update migrations for country references.
  - Review and update the database schema if necessary.
  - Test functionality to ensure foreign key relations are properly handled with country codes.

---

## 7. [ ] Implement standardized filtering and sorting with Spatie Query Builder to index method in all CRUD controllers. 

Create trait
```php
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
```php
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

Use this trait in controllers, fill `$filters` with all columns of domain model, and some relations if needed. Use DateRangeFilter for date fields.

Create something like `Search[model]Request` with validation for those filters.

Add tests.

---

## 8. [ ] Implement user settings and preferences as part of the user profile.

- **Subtasks**:
  - Implement a user settings model to store preferences (e.g., language, timezone, notification preferences).
  - Create actions for updating user settings (language, timezone, etc.).
  - Update user profile interface to allow settings modification.
  - Add validation for user settings actions.
  - Create migration for user settings table.
  - Add tests for user settings actions.

---

## 9. [ ] Implement task-based notifications for users (e.g., for project deadlines, task assignments).

- **Subtasks**:
  - Define task-related notifications.
  - Set up notification system using Laravel Notifications.
  - Trigger notifications based on task events (task created, updated, etc.).
  - Allow users to customize notification preferences.
  - Add tests for notification functionality.

---

## 10. [ ] Add multi-language support for user interface.

- **Subtasks**:
  - Implement language files for UI translations.
  - Set up language switching feature in the UI.
  - Ensure all UI strings are translatable.
  - Test multi-language support across the application.

---