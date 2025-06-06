## 🔍 Task: Full-Text Search Implementation (Meilisearch + Laravel Scout)

### 🎯 Goal

Implement full-text search in the application using **Meilisearch** + **Laravel Scout**, with:

- `docker-compose` setup for Meilisearch
- Scout integration in Laravel
- Making the following models searchable:
  - `Contractor`
  - `Product`
  - `Invoice`
  - `User`
  - `Contact`
- A dedicated `search(string $query)` method in each controller
- REST endpoint: `GET /api/{model}/search?q=...`

---

# Full-Text Search Implementation Plan

## 1. Dependencies Installation

```bash
composer require laravel/scout meilisearch/meilisearch-php
```

## 2. Configuration

### 2.1. Scout Configuration
Create `config/scout.php`:
```php
<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'queue' => env('SCOUT_QUEUE', true),
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', 'masterKey'),
        'index-settings' => [
            'Contractor' => [
                'filterableAttributes' => ['tenant_id', 'is_active'],
                'sortableAttributes' => ['created_at', 'updated_at'],
            ],
            'Product' => [
                'filterableAttributes' => ['tenant_id', 'unit_id', 'vat_rate_id'],
                'sortableAttributes' => ['created_at', 'updated_at'],
            ],
            'Invoice' => [
                'filterableAttributes' => ['tenant_id', 'status', 'due_date'],
                'sortableAttributes' => ['created_at', 'updated_at', 'due_date'],
            ],
            'User' => [
                'filterableAttributes' => ['tenant_id', 'is_active'],
                'sortableAttributes' => ['created_at', 'updated_at'],
            ],
            'Contact' => [
                'filterableAttributes' => ['tenant_id', 'contactable_type', 'contactable_id'],
                'sortableAttributes' => ['created_at', 'updated_at'],
            ],
        ],
    ],
];
```

### 2.2. Environment Variables
Add to `.env`:
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey
```

### 2.3. Docker Configuration
Add to `docker-compose.yml`:
```yaml
services:
  meilisearch:
    image: getmeili/meilisearch:latest
    ports:
      - "7700:7700"
    environment:
      MEILI_NO_ANALYTICS: "true"
      MEILI_MASTER_KEY: "masterKey"
    volumes:
      - meilisearch_data:/meili_data
    networks:
      - app-network

volumes:
  meilisearch_data:
```

## 3. Model Updates

### 3.1. Create Searchable Trait
Create `app/Domain/Common/Traits/IsSearchable.php`:
```php
<?php

namespace App\Domain\Common\Traits;

use Laravel\Scout\Searchable;

trait IsSearchable
{
    use Searchable;

    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        
        // Remove sensitive data
        unset($array['password'], $array['remember_token']);
        
        return $array;
    }

    public function shouldBeSearchable(): bool
    {
        return true;
    }
}
```

### 3.2. Update Models
Add the trait to each model:

```php
use App\Domain\Common\Traits\IsSearchable;

class Contractor extends Model
{
    use IsSearchable;
    // ...
}

class Product extends Model
{
    use IsSearchable;
    // ...
}

class Invoice extends Model
{
    use IsSearchable;
    // ...
}

class User extends Model
{
    use IsSearchable;
    // ...
}

class Contact extends Model
{
    use IsSearchable;
    // ...
}
```

## 4. Controller Updates

### 4.1. Add Search Methods to Existing Controllers

#### ContractorController
```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = $request->input('q');
    $perPage = $request->input('perPage', $this->defaultPerPage);

    if (!$query) {
        return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
    }

    $results = Contractor::search($query)
        ->query(function ($builder) use ($request) {
            return $this->getIndexQuery($request);
        })
        ->paginate($perPage);

    return ContractorResource::collection($results);
}
```

#### ProductController
```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = $request->input('q');
    $perPage = $request->input('perPage', $this->defaultPerPage);

    if (!$query) {
        return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
    }

    $results = Product::search($query)
        ->query(function ($builder) use ($request) {
            return $this->getIndexQuery($request);
        })
        ->paginate($perPage);

    return ProductResource::collection($results);
}
```

#### InvoiceController
```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = $request->input('q');
    $perPage = $request->input('perPage', $this->defaultPerPage);

    if (!$query) {
        return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
    }

    $results = Invoice::search($query)
        ->query(function ($builder) use ($request) {
            return $this->getIndexQuery($request);
        })
        ->paginate($perPage);

    return InvoiceResource::collection($results);
}
```

#### UserController
```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = $request->input('q');
    $perPage = $request->input('perPage', $this->defaultPerPage);

    if (!$query) {
        return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
    }

    $results = User::search($query)
        ->query(function ($builder) use ($request) {
            return $this->getIndexQuery($request);
        })
        ->paginate($perPage);

    return UserResource::collection($results);
}
```

#### ContactController
```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = $request->input('q');
    $perPage = $request->input('perPage', $this->defaultPerPage);

    if (!$query) {
        return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
    }

    $results = Contact::search($query)
        ->query(function ($builder) use ($request) {
            return $this->getIndexQuery($request);
        })
        ->paginate($perPage);

    return ContactResource::collection($results);
}
```

### 4.2. Add Routes
Add to `routes/api.php`:
```php
Route::get('contractors/search', [ContractorController::class, 'search'])->name('api.contractors.search');
Route::get('products/search', [ProductController::class, 'search'])->name('api.products.search');
Route::get('invoices/search', [InvoiceController::class, 'search'])->name('api.invoices.search');
Route::get('users/search', [UserController::class, 'search'])->name('api.users.search');
Route::get('contacts/search', [ContactController::class, 'search'])->name('api.contacts.search');
```

## 5. Indexing

### 5.1. Create Index Command
Create `app/Console/Commands/ScoutIndexCommand.php`:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Common\Models\User;
use App\Domain\Common\Models\Contact;

class ScoutIndexCommand extends Command
{
    protected $signature = 'scout:index-all';
    protected $description = 'Index all searchable models';

    public function handle()
    {
        $models = [
            Contractor::class,
            Product::class,
            Invoice::class,
            User::class,
            Contact::class,
        ];

        foreach ($models as $model) {
            $this->info("Indexing {$model}...");
            $model::makeAllSearchable();
            $this->info("Indexed {$model}");
        }

        $this->info('All models have been indexed.');
    }
}
```

## 6. Testing

### 6.1. Create Search Tests
Create `tests/Feature/SearchTest.php`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Common\Models\User;
use App\Domain\Common\Models\Contact;

class SearchTest extends TestCase
{
    public function test_can_search_contractors()
    {
        $contractor = Contractor::factory()->create(['name' => 'Test Contractor']);
        
        $response = $this->getJson('/api/contractors/search?q=Test');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // Add similar tests for other models
}
```

## 7. Documentation

### 7.1. API Documentation
Add to API documentation:
```markdown
## Search API

### Search Endpoints

#### Contractors
`GET /api/contractors/search?q={query}`

#### Products
`GET /api/products/search?q={query}`

#### Invoices
`GET /api/invoices/search?q={query}`

#### Users
`GET /api/users/search?q={query}`

#### Contacts
`GET /api/contacts/search?q={query}`

#### Parameters
- `q` (required): Search query string
- `perPage` (optional): Number of results per page (default: 15)

#### Example
```
GET /api/contractors/search?q=test&perPage=20
```
```

## 8. Deployment Steps

1. Add Meilisearch service to production environment
2. Set up proper environment variables
3. Run initial indexing:
```bash
php artisan scout:index-all
```
4. Set up queue worker for Scout indexing:
```bash
php artisan queue:work --queue=scout
```

## 9. Monitoring

1. Add Meilisearch monitoring to application monitoring
2. Set up alerts for:
   - Meilisearch service availability
   - Indexing queue size
   - Search response times
   - Error rates

## 10. Maintenance

1. Regular index optimization:
```bash
php artisan scout:optimize
```

2. Index cleanup:
```bash
php artisan scout:flush
```

3. Regular monitoring of:
   - Index size
   - Search performance
   - Error rates
   - Queue backlog
