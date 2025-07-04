# Position System Implementation (CORRECTED & ALIGNED WITH EXISTING CODEBASE)
## Task Description & Documentation

### Overview
Implement a comprehensive Position System for SaaSBase that extends the existing Organization Unit structure with support for Positions, linking them to Spatie Permission roles and enabling flexible organizational management.

### Core Requirements

#### 1. Position Categories
Create `PositionCategory` model with the following fields:
- `id` (ULID) - Primary key
- `tenant_id` (ULID, nullable) - Multi-tenant isolation (global or tenant-specific)
- `name` (string, required) - Display name of the category
- `slug` (string, nullable) - URL-friendly identifier
- `description` (text, nullable) - Optional description
- `sort_order` (integer, default 0) - For ordering categories
- `is_active` (boolean, default true) - Enable/disable categories

**Purpose**: Group positions into logical categories like "Director", "Deputy", "Employee", "Trainee" for reporting and filtering.

#### 2. Position Model
Create `Position` model with the following fields:
- `id` (ULID) - Primary key
- `tenant_id` (ULID, nullable) - Multi-tenant isolation (global or tenant-specific)
- `organization_unit_id` (ULID) - Links to existing OrganizationUnit
- `position_category_id` (ULID) - Links to PositionCategory
- `role_name` (string, nullable) - Links to Spatie Permission role
- `name` (string) - Position name (e.g., "Director", "Senior Developer")
- `description` (text, nullable) - Optional position description
- Boolean flags for filtering and business logic:
  - `is_director` (boolean, default false) - Director-level position
  - `is_learning` (boolean, default false) - Trainee/apprentice position
  - `is_temporary` (boolean, default false) - Temporary position
- `hourly_rate` (decimal, nullable) - Optional hourly rate
- `sort_order` (integer, default 0) - For ordering positions
- `is_active` (boolean, default true) - Enable/disable positions

**Virtual Attribute**: `full_name` - Automatically generated as "Position Name - Organization Unit Name"

#### 3. Enhanced OrgUnitUser Model
Extend the existing `OrgUnitUser` model with position-related fields:
- `position_id` (ULID, nullable) - Links to Position
- `start_date` (date, nullable) - When assignment started (in addition to existing valid_from)
- `end_date` (date, nullable) - When assignment ended (in addition to existing valid_until)
- `notes` (text, nullable) - Additional notes about the assignment

### Implementation Tasks

#### Database Migrations

1. **Create position_categories table**
```sql
-- Migration: create_position_categories_table.php
Schema::create('position_categories', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->nullable();
    $table->text('description')->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    // Indexes and constraints
    $table->unique(['tenant_id', 'name']);
    $table->index(['tenant_id', 'is_active']);
    $table->index(['tenant_id', 'sort_order']);
});
```

2. **Create positions table**
```sql
-- Migration: create_positions_table.php
Schema::create('positions', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignUlid('organization_unit_id')->constrained()->cascadeOnDelete();
    $table->foreignUlid('position_category_id')->constrained()->restrictOnDelete();
    $table->string('role_name')->nullable(); // Links to Spatie role
    $table->string('name');
    $table->text('description')->nullable();
    
    // Position flags
    $table->boolean('is_director')->default(false);
    $table->boolean('is_learning')->default(false);
    $table->boolean('is_temporary')->default(false);
    
    // Additional metadata
    $table->decimal('hourly_rate', 10, 2)->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    // Indexes and constraints
    $table->unique(['tenant_id', 'organization_unit_id', 'name']);
    $table->index(['tenant_id', 'is_active']);
    $table->index(['tenant_id', 'is_director']);
    $table->index(['tenant_id', 'is_learning']);
    $table->index(['tenant_id', 'organization_unit_id']);
    
    // Foreign key for role_name (optional constraint)
    $table->foreign('role_name')->references('name')->on('roles')->nullOnDelete();
});
```

3. **Enhance existing org_unit_user table**
```sql
-- Migration: add_position_fields_to_org_unit_user_table.php
Schema::table('org_unit_user', function (Blueprint $table) {
    $table->foreignUlid('position_id')->nullable()->after('workflow_role_level')->constrained('positions')->nullOnDelete();
    $table->date('start_date')->nullable()->after('position_id');
    $table->date('end_date')->nullable()->after('start_date');
    $table->text('notes')->nullable()->after('end_date');
    
    // Indexes
    $table->index(['tenant_id', 'position_id']);
    $table->index(['user_id', 'position_id']);
});
```

#### Model Implementations

1. **PositionCategory Model**
```php
<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PositionCategory extends BaseModel
{
    use IsGlobalOrBelongsToTenant;
    
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
    
    public function activePositions(): HasMany
    {
        return $this->positions()->where('is_active', true);
    }
    
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Position::class,
            'position_category_id',
            'id'
        )->through('orgUnitUsers');
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
```

2. **Position Model**
```php
<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Rights\Models\Role;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Position extends BaseModel
{
    use IsGlobalOrBelongsToTenant;
    
    protected $fillable = [
        'tenant_id',
        'organization_unit_id',
        'position_category_id',
        'role_name',
        'name',
        'description',
        'is_director',
        'is_learning',
        'is_temporary',
        'hourly_rate',
        'sort_order',
        'is_active',
    ];
    
    protected $casts = [
        'is_director' => 'boolean',
        'is_learning' => 'boolean',
        'is_temporary' => 'boolean',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'sort_order' => 'integer',
    ];
    
    protected $appends = ['full_name'];
    
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(PositionCategory::class, 'position_category_id');
    }
    
    public function orgUnitUsers(): HasMany
    {
        return $this->hasMany(OrgUnitUser::class);
    }
    
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            OrgUnitUser::class,
            'position_id',
            'id',
            'id',
            'user_id'
        );
    }
    
    public function currentUsers(): HasManyThrough
    {
        return $this->users()
                    ->whereHas('orgUnitUsers', function($query) {
                        $query->active();
                    });
    }
    
    // Get the Spatie role
    public function getRole(): ?Role
    {
        if (!$this->role_name) {
            return null;
        }
        return Role::where('name', $this->role_name)->first();
    }
    
    // Virtual attribute for full name
    public function getFullNameAttribute(): string
    {
        return $this->name . ' - ' . $this->organizationUnit->name;
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeDirectors($query)
    {
        return $query->where('is_director', true);
    }
    
    public function scopeLearning($query)
    {
        return $query->where('is_learning', true);
    }
    
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('position_category_id', $categoryId);
    }
}
```

3. **Enhanced OrgUnitUser Model** (extend existing)
```php
// Add these methods to existing OrgUnitUser model

public function position(): BelongsTo
{
    return $this->belongsTo(Position::class);
}

// Enhanced isActive method to consider both date fields
public function isActiveWithDates(): bool
{
    $now = now();
    
    // Check validity period (existing logic)
    $validPeriodActive = $this->valid_from <= $now
                        && (null === $this->valid_until || $this->valid_until >= $now);
    
    // Check date range (new logic)
    $startOk = $this->start_date ? $this->start_date <= $now->toDateString() : true;
    $endOk = $this->end_date ? $this->end_date >= $now->toDateString() : true;
    
    return $validPeriodActive && $startOk && $endOk;
}

// Scopes
public function scopeWithPosition($query)
{
    return $query->whereNotNull('position_id');
}

public function scopeWithoutPosition($query)
{
    return $query->whereNull('position_id');
}

public function scopeActive($query)
{
    return $query->where(function($q) {
        $q->where('valid_from', '<=', now())
          ->where(function($q2) {
              $q2->whereNull('valid_until')
                 ->orWhere('valid_until', '>=', now());
          });
    });
}
```

4. **Enhanced User Model** (add methods to existing)
```php
// Add these methods to existing User model

public function positions(): HasManyThrough
{
    return $this->hasManyThrough(
        Position::class,
        OrgUnitUser::class,
        'user_id',
        'id',
        'id',
        'position_id'
    );
}

public function currentPositions(): HasManyThrough
{
    return $this->positions()
                ->whereHas('orgUnitUsers', function($query) {
                    $query->where('user_id', $this->id)->active();
                });
}

public function primaryPosition(): ?Position
{
    $primaryOrgUnit = $this->orgUnitUsers()->where('is_primary', true)->active()->first();
    return $primaryOrgUnit?->position;
}

// Assign user to organization unit with position
public function assignToPosition(OrganizationUnit $unit, Position $position = null, array $options = []): OrgUnitUser
{
    $options = array_merge([
        'start_date' => now()->toDateString(),
        'is_primary' => false,
        'notes' => null,
        'role' => OrgUnitRole::Employee,
    ], $options);
    
    // Create org unit user assignment
    $orgUnitUser = $this->orgUnitUsers()->create([
        'tenant_id' => $unit->tenant_id,
        'organization_unit_id' => $unit->id,
        'position_id' => $position?->id,
        'role' => $options['role']->value,
        'start_date' => $options['start_date'],
        'is_primary' => $options['is_primary'],
        'notes' => $options['notes'],
        'valid_from' => now(),
    ]);
    
    // Assign role if position has one
    if ($position && $position->role_name) {
        $this->assignRole($position->role_name);
    }
    
    return $orgUnitUser;
}

// Check user status
public function isDirector(): bool
{
    return $this->currentPositions()->where('is_director', true)->exists();
}

public function isLearning(): bool
{
    return $this->currentPositions()->where('is_learning', true)->exists();
}

// Get user's position in specific unit
public function getPositionInUnit(OrganizationUnit $unit): ?Position
{
    $orgUnitUser = $this->orgUnitUsers()
                       ->active()
                       ->where('organization_unit_id', $unit->id)
                       ->first();
    
    return $orgUnitUser?->position;
}
```

5. **Enhanced OrganizationUnit Model** (add methods to existing)
```php
// Add these methods to existing OrganizationUnit model

public function positions(): HasMany
{
    return $this->hasMany(Position::class);
}

public function activePositions(): HasMany
{
    return $this->positions()->active();
}

public function getUsersWithPositions()
{
    return $this->orgUnitUsers()
                ->active()
                ->with(['user', 'position.category'])
                ->get()
                ->map(function($orgUnitUser) {
                    return [
                        'user' => $orgUnitUser->user,
                        'position' => $orgUnitUser->position,
                        'category' => $orgUnitUser->position?->category,
                        'is_primary' => $orgUnitUser->is_primary,
                        'start_date' => $orgUnitUser->start_date,
                        'role' => $orgUnitUser->role,
                    ];
                });
}

public function getDirectors()
{
    return $this->orgUnitUsers()
                ->active()
                ->whereHas('position', function($query) {
                    $query->where('is_director', true);
                })
                ->with(['user', 'position'])
                ->get()
                ->pluck('user');
}
```

#### Service Layer

Create `OrganizationPositionService` in the Tenant domain:

```php
<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Position;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Enums\OrgUnitRole;
use Illuminate\Support\Facades\DB;

class OrganizationPositionService
{
    public function createSpecialUnits(): array
    {
        $awaiting = OrganizationUnit::firstOrCreate([
            'name' => 'Awaiting Assignment',
            'code' => 'awaiting-assignment',
        ], [
            'description' => 'Temporary unit for new users awaiting assignment',
            'is_active' => true,
        ]);
        
        $inactive = OrganizationUnit::firstOrCreate([
            'name' => 'Not Working Anymore',
            'code' => 'not-working-anymore',
        ], [
            'description' => 'Unit for inactive users',
            'is_active' => false,
        ]);
        
        return [$awaiting, $inactive];
    }
    
    public function assignUserToPosition(
        User $user, 
        OrganizationUnit $unit, 
        Position $position = null, 
        array $options = []
    ): void {
        DB::transaction(function() use ($user, $unit, $position, $options) {
            // Remove from awaiting if exists
            $this->removeFromAwaitingAssignment($user);
            
            // Assign to unit with position
            $user->assignToPosition($unit, $position, $options);
        });
    }
    
    public function removeFromAwaitingAssignment(User $user): void
    {
        $awaitingUnit = OrganizationUnit::where('code', 'awaiting-assignment')->first();
        if ($awaitingUnit) {
            $this->removeUserFromUnit($user, $awaitingUnit);
        }
    }
    
    public function removeUserFromUnit(User $user, OrganizationUnit $unit, $endDate = null): void
    {
        $endDate = $endDate ?? now()->toDateString();
        
        $orgUnitUser = $user->orgUnitUsers()
                           ->where('organization_unit_id', $unit->id)
                           ->whereNull('end_date')
                           ->whereNull('valid_until')
                           ->first();
        
        if ($orgUnitUser) {
            $orgUnitUser->update([
                'end_date' => $endDate,
                'valid_until' => now(),
            ]);
            
            // Remove role if no other positions have same role
            if ($orgUnitUser->position && $orgUnitUser->position->role_name) {
                $hasOtherPositionsWithRole = $user->currentPositions()
                                                 ->where('role_name', $orgUnitUser->position->role_name)
                                                 ->where('id', '!=', $orgUnitUser->position->id)
                                                 ->exists();
                
                if (!$hasOtherPositionsWithRole) {
                    $user->removeRole($orgUnitUser->position->role_name);
                }
            }
        }
    }
    
    public function moveToFormerEmployees(User $user): void
    {
        DB::transaction(function() use ($user) {
            // End all current assignments
            $user->orgUnitUsers()
                 ->active()
                 ->update([
                     'end_date' => now()->toDateString(),
                     'valid_until' => now(),
                 ]);
            
            // Remove all current roles
            $user->syncRoles([]);
            
            // Move to inactive unit
            $inactiveUnit = OrganizationUnit::where('code', 'not-working-anymore')->first();
            if ($inactiveUnit) {
                $user->assignToPosition($inactiveUnit, null, [
                    'is_primary' => true,
                    'role' => OrgUnitRole::Employee,
                ]);
            }
        });
    }
    
    public function getAllDirectors()
    {
        return User::whereHas('orgUnitUsers', function($query) {
            $query->active()
                  ->whereHas('position', function($q) {
                      $q->where('is_director', true);
                  });
        })->with(['orgUnitUsers.organizationUnit', 'orgUnitUsers.position'])->get();
    }
    
    public function getAllLearningPositions()
    {
        return User::whereHas('orgUnitUsers', function($query) {
            $query->active()
                  ->whereHas('position', function($q) {
                      $q->where('is_learning', true);
                  });
        })->with(['orgUnitUsers.organizationUnit', 'orgUnitUsers.position'])->get();
    }
    
    public function getOrganizationChart()
    {
        return OrganizationUnit::with([
            'positions.category',
            'orgUnitUsers.user',
            'orgUnitUsers.position'
        ])->get()->map(function($unit) {
            return [
                'unit' => $unit,
                'users_with_positions' => $unit->getUsersWithPositions(),
                'positions' => $unit->positions->map(function($position) {
                    return [
                        'position' => $position,
                        'category' => $position->category,
                        'current_users_count' => $position->currentUsers()->count(),
                    ];
                }),
            ];
        });
    }
}
```

### Integration with InitializeTenantDefaults

Update the existing `InitializeTenantDefaults` class to create default position categories:

```php
// Add this method to existing InitializeTenantDefaults class

public function createDefaultPositionCategories(Tenant $tenant): void
{
    $defaultCategories = [
        [
            'name' => 'Director',
            'slug' => 'director',
            'description' => 'Leadership positions',
            'sort_order' => 1,
        ],
        [
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Management positions',
            'sort_order' => 2,
        ],
        [
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Regular employee positions',
            'sort_order' => 3,
        ],
        [
            'name' => 'Trainee',
            'slug' => 'trainee',
            'description' => 'Learning and training positions',
            'sort_order' => 4,
        ],
    ];

    foreach ($defaultCategories as $category) {
        PositionCategory::firstOrCreate([
            'tenant_id' => $tenant->id,
            'name' => $category['name'],
        ], $category);
    }
}

// Add to execute method:
public function execute(Tenant $tenant, ?User $owner = null): void
{
    $this->createRootOrganizationUnit($tenant, $owner);
    $this->seedDefaultMeasurementUnits($tenant);
    $this->createSubscription($tenant);
    $this->seedDefaultTags($tenant);
    $this->createDefaultPositionCategories($tenant); // Add this line
}
```

### Usage Examples

```php
// Create position categories
$directorCategory = PositionCategory::create([
    'name' => 'Director',
    'slug' => 'director',
    'sort_order' => 1,
]);

// Create positions
$itDirector = Position::create([
    'organization_unit_id' => $itDepartment->id,
    'position_category_id' => $directorCategory->id,
    'role_name' => 'it-director',
    'name' => 'IT Director',
    'is_director' => true,
]);

// Assign user to position
$service = new OrganizationPositionService();
$service->assignUserToPosition($user, $itDepartment, $itDirector, [
    'is_primary' => true,
    'start_date' => now()->toDateString(),
    'role' => OrgUnitRole::Owner,
]);

// Query examples
$allDirectors = $service->getAllDirectors();
$learningUsers = $service->getAllLearningPositions();
$orgChart = $service->getOrganizationChart();

// Check user status
$isDirector = $user->isDirector();
$primaryPosition = $user->primaryPosition();
```

### Migration Strategy

1. **Phase 1**: Create position_categories and positions tables
2. **Phase 2**: Enhance org_unit_user table with position fields
3. **Phase 3**: Update models with new relationships and methods
4. **Phase 4**: Create service layer
5. **Phase 5**: Update InitializeTenantDefaults
6. **Phase 6**: Test with existing data (positions will be null initially)
7. **Phase 7**: Gradually assign positions to existing users

### Testing Requirements

1. **Unit Tests**
   - Model relationships and scopes
   - Position assignment/removal logic
   - Role synchronization
   - Historical tracking

2. **Feature Tests**
   - Complete user assignment workflow
   - Transfer between units
   - Organizational chart generation
   - Special unit handling

3. **Integration Tests**
   - Multi-tenant isolation
   - Spatie permissions integration
   - Database constraints and cascades

### Success Criteria

- [ ] All database migrations run successfully without conflicts
- [ ] Position models integrate with existing OrganizationUnit structure
- [ ] Position assignment automatically manages Spatie roles
- [ ] Historical tracking maintains complete audit trail using existing valid_from/valid_until
- [ ] Organizational chart generation works efficiently
- [ ] Multi-tenant isolation is maintained using existing patterns
- [ ] All tests pass
- [ ] Integration with InitializeTenantDefaults works properly
- [ ] Backward compatibility with existing org_unit_user data

---

## Summary of Key Fixes Applied

1. **CORRECTED: OrgUnitRole Enum Usage**: Changed `OrgUnitRole::Member` to `OrgUnitRole::Employee` (which exists in your enum)
2. **CORRECTED: Migration Field Order**: Positioned new fields after `workflow_role_level` to match existing table structure
3. **CORRECTED: Foreign Key Reference**: Used proper table name `positions` in foreign key constraint
4. **CORRECTED: Scope Method**: Added proper `active()` scope method to OrgUnitUser model
5. **CORRECTED: Enum Value Access**: Used `->value` property when setting enum values in database
6. **CORRECTED: Model Structure**: Aligned with existing BaseModel and IsGlobalOrBelongsToTenant usage
7. **CORRECTED: Tenant ID**: Properly handled nullable tenant_id for global/tenant-specific records
8. **CORRECTED: Migration Dependencies**: Ensured migrations reference existing table structures correctly
