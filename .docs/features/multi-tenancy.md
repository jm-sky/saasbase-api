# Multi-tenancy Implementation

## Overview
The system implements multi-tenancy using a global scope to filter data based on tenant ID. This ensures data isolation between different tenants while allowing flexibility for system-wide operations when needed.

## Core Components

### BelongsToTenant Trait
The `BelongsToTenant` trait automatically applies tenant scoping to models:

```php
namespace App\Domain\Tenant\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\TenantScope;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
```

### TenantScope Implementation
Global scope that filters queries by tenant:

```php
namespace App\Domain\Tenant\Scopes;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\TenantNotFoundException;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var ?User $user */
        $user     = Auth::user();
        $tenantId = $user?->getTenantId() ?? Tenant::$PUBLIC_TENANT_ID;

        $builder->where('tenant_id', $tenantId);
    }
}
```

### Exception Handling
Custom exception for tenant context issues:

```php
namespace App\Domain\Tenant\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class TenantNotFoundException extends \Exception
{
    protected $message = 'Tenant context not found. Please ensure the user is properly authenticated for the correct tenant.';

    public function render($request): JsonResponse
    {
        return ResponseFacade::json([
            'error' => $this->message,
        ], Response::HTTP_FORBIDDEN);
    }
}

```

## Dictionary Tables in Multi-tenant Context

For dictionary-like tables (e.g., VAT rates), we use meaningful string primary keys instead of UUIDs to improve readability and maintainability.

### Implementation Details

1. **Primary Keys**: Use string values as primary keys:
   - VAT rates: `'5%'`, `'23%'`, `'0%'`
   - Countries: `'PL'`, `'DE'`, `'US'`
   - Units: `'kg'`, `'pcs'`, `'h'`

2. **Foreign Key Updates**:
   - Replace UUID foreign keys with string references
   - Example: Change `vat_rate_id` to `vat_rate` (string)

3. **Model Relationships**:
```php
// In Product model
public function vatRate()
{
    return $this->belongsTo(VatRate::class, 'vat_rate', 'rate');
}
```

## Security Considerations

1. Tenant ID is stored securely in:
   - Session data
   - JWT claims
   - Request context

2. Global scope ensures:
   - Automatic filtering of all queries
   - No accidental data leaks
   - Proper isolation between tenants

## Testing

1. Unit tests should cover:
   - Scope application
   - Tenant context validation
   - Exception handling

2. Feature tests should verify:
   - Data isolation between tenants
   - Proper scoping in relationships
   - Dictionary table relationships 
