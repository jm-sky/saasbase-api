# AllocationDimensionInterface Implementation Guide

This document explains how to implement the `AllocationDimensionInterface` for different types of models used in the allocation system.

## Standard Dimension Models

For models that follow the standard dimension pattern (having `id`, `tenant_id`, `code`, `name`, `description`, `is_active` properties):

```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Contracts\AllocationDimensionInterface;
use App\Domain\Expense\Traits\HasAllocationDimensionInterface;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;

class AllocationTransactionType extends BaseModel implements AllocationDimensionInterface
{
    use IsGlobalOrBelongsToTenant;
    use HasAllocationDimensionInterface; // Provides default implementation

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Optional: Custom display name logic
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
```

## Custom Models (e.g., User for EMPLOYEES dimension)

For models that don't follow the standard dimension pattern, implement the interface manually:

```php
<?php

namespace App\Domain\Auth\Models;

use App\Domain\Expense\Contracts\AllocationDimensionInterface;

class User extends BaseModel implements AllocationDimensionInterface
{
    // ... existing User model code ...

    // Implement AllocationDimensionInterface
    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->employee_code ?? null; // or null if no code concept
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->email ?? null; // or bio, or null
    }

    public function getTenantId(): ?string
    {
        return $this->tenant_id ?? null;
    }

    public function getIsActive(): bool
    {
        return $this->is_active ?? true;
    }

    public function getDisplayName(): string
    {
        return $this->name . ' (' . $this->email . ')';
    }

    public function isGlobal(): bool
    {
        return false; // Users are typically not global
    }
}
```

## Project Model (for PROJECT dimension)

```php
<?php

namespace App\Domain\Projects\Models;

use App\Domain\Expense\Contracts\AllocationDimensionInterface;

class Project extends BaseModel implements AllocationDimensionInterface
{
    // ... existing Project model code ...

    // Implement AllocationDimensionInterface
    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code ?? $this->slug ?? null;
    }

    public function getName(): ?string
    {
        return $this->name ?? $this->title ?? null;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTenantId(): ?string
    {
        return $this->tenant_id;
    }

    public function getIsActive(): bool
    {
        return $this->is_active ?? $this->status === 'active';
    }

    public function getDisplayName(): string
    {
        return $this->name ?? $this->title ?? $this->id;
    }

    public function isGlobal(): bool
    {
        return null === $this->tenant_id;
    }
}
```

## Notes

1. **Use the trait when possible**: The `HasAllocationDimensionInterface` trait provides sensible defaults for standard dimension models.

2. **Custom implementation for special cases**: Models like User or Project may need custom implementations that map their unique properties to the interface requirements.

3. **Display name logic**: The `getDisplayName()` method should return a human-readable string that's useful in dropdowns and lists. This can be customized per model.

4. **Backwards compatibility**: Existing models don't need to change their public API - the interface methods are additional and can coexist with existing getters.

5. **Testing**: Ensure that models implementing this interface work correctly with `DimensionItemResource` and the allocation system. 
