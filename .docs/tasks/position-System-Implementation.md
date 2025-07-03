# Position System Implementation
## Task Description & Documentation

### Overview
Implement a comprehensive Position System for SaaSBase that extends the existing Organization Unit structure with support for Positions, linking them to Spatie Permission roles and enabling flexible organizational management.

### Core Requirements

#### 1. Position Categories
- Create `PositionCategory` model with the following fields:
  - `name` (string, required) - Display name of the category
  - `slug` (string, nullable) - URL-friendly identifier
  - `description` (text, nullable) - Optional description
  - `sort_order` (integer, default 0) - For ordering categories
  - `is_active` (boolean, default true) - Enable/disable categories
  - `tenant_id` (string) - Multi-tenant isolation

**Purpose**: Group positions into logical categories like "Director", "Deputy", "Employee", "Trainee" for reporting and filtering.

#### 2. Position Model
- Create `Position` model with the following fields:
  - `tenant_id` (string) - Multi-tenant isolation
  - `organization_unit_id` (foreign key) - Links to existing OrganizationUnit
  - `position_category_id` (foreign key) - Links to PositionCategory
  - `role_name` (string) - Links to Spatie Permission role
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
- `position_id` (foreign key, nullable) - Links to Position
- `start_date` (date, nullable) - When assignment started
- `end_date` (date, nullable) - When assignment ended (null = active)
- `is_primary` (boolean, default false) - Primary position for users with multiple assignments
- `notes` (text, nullable) - Additional notes about the assignment

### Implementation Tasks

#### Database Migrations

1. **Create position_categories table**
```sql
- id, tenant_id, name, slug, description, sort_order, is_active, timestamps
- Unique constraint on [tenant_id, name]
- Index on [tenant_id, is_active]
```

2. **Create positions table**
```sql
- id, tenant_id, organization_unit_id, position_category_id, role_name, name, description
- Boolean flags: is_director, is_learning, is_temporary, is_contractor
- hourly_rate, sort_order, is_active, timestamps
- Foreign key constraints with proper cascade/restrict rules
- Unique constraint on [tenant_id, organization_unit_id, name]
- Indexes on tenant_id with various flag combinations
```

3. **Alter org_unit_users table**
```sql
- Add: position_id (nullable FK), start_date, end_date, is_primary, notes
- Add indexes for efficient querying
```

#### Model Relationships

1. **PositionCategory Model**
   - `hasMany(Position::class)` - All positions in this category
   - `activePositions()` - Active positions only
   - `users()` - Users through positions (hasManyThrough)
   - Scopes: `active()`, `ordered()`

2. **Position Model**
   - `belongsTo(OrganizationUnit::class)` - Parent organization unit
   - `belongsTo(PositionCategory::class)` - Position category
   - `hasMany(OrgUnitUser::class)` - Direct assignments
   - `users()` - Users through OrgUnitUser (hasManyThrough)
   - `currentUsers()` - Currently assigned users only
   - `getRole()` - Get associated Spatie role
   - Scopes: `active()`, `directors()`, `learning()`, `byCategory()`

3. **Enhanced OrgUnitUser Model**
   - `belongsTo(Position::class)` - Assigned position
   - `isActive()` - Check if assignment is currently active
   - Scopes: `active()`, `primary()`, `current()`, `withPosition()`

4. **Enhanced User Model**
   - `orgUnitUsers()` - All org unit assignments
   - `currentOrgUnitUsers()` - Active assignments only
   - `positions()` - All positions (hasManyThrough)
   - `currentPositions()` - Currently assigned positions
   - `primaryPosition()` - Primary position
   - `assignToUnit()` - Assign to unit with position
   - `removeFromUnit()` - Remove from unit
   - `updatePosition()` - Change position within unit
   - `isDirector()`, `isLearning()` - Status checks
   - `getPositionInUnit()` - Get position in specific unit

5. **Enhanced OrganizationUnit Model**
   - `positions()` - All positions in this unit
   - `orgUnitUsers()` - All user assignments
   - `currentOrgUnitUsers()` - Active assignments
   - `directors()` - Users with director positions
   - `getUsersWithPositions()` - Detailed user-position mapping

#### Service Layer

Create `OrganizationStructureService` with methods:
- `createSpecialUnits()` - Create "Awaiting Assignment" and "Not Working Anymore" units
- `assignUserToPosition()` - Assign user to unit with position
- `removeFromAwaitingAssignment()` - Remove from awaiting unit
- `moveToInactive()` - Move user to inactive status
- `transferUser()` - Transfer between units
- `getAllDirectors()` - Get all users with director positions
- `getAllLearningPositions()` - Get all users in learning positions
- `getOrganizationChart()` - Complete organizational structure

**Usage** - `createSpecialUnits` should be used in `InitializeTenantDefaults` class 

### Special Organization Units

1. **"Awaiting Assignment"**
   - For new users not yet assigned to specific units
   - `slug: 'awaiting-assignment'`
   - `is_active: true, is_special: true`

2. **"Not Working Anymore"**
   - For inactive/terminated users
   - `slug: 'not-working-anymore'`
   - `is_active: false, is_special: true`

### Key Features

#### Automatic Role Management
- When assigning position: automatically assign associated Spatie role
- When removing position: remove role if no other positions require it
- Handle role conflicts when updating positions

#### Historical Tracking
- Track start/end dates for all assignments
- Maintain position history for reporting
- Support for temporary assignments

#### Flexible Querying
- Get all directors across organization
- Filter by position categories
- Find learning/temporary positions
- Generate organizational charts

#### Multi-tenant Support
- All models include `tenant_id` for isolation
- Proper tenant scoping on all queries
- Tenant-specific position categories and roles

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
$service = new OrganizationStructureService();
$service->assignUserToPosition($user, $itDepartment, $itDirector, [
    'is_primary' => true,
    'start_date' => now()->toDateString(),
]);

// Query all directors
$allDirectors = $service->getAllDirectors();

// Get organizational chart
$orgChart = $service->getOrganizationChart();
```

### Migration Strategy

1. **Phase 1**: Create new tables and add fields to existing tables
2. **Phase 2**: Implement models and relationships
3. **Phase 3**: Create service layer and helper methods
4. **Phase 4**: Create special organization units
5. **Phase 5**: Test with existing data (positions will be null initially)
6. **Phase 6**: Gradually assign positions to existing users

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

### Performance Considerations

1. **Indexes**
   - Composite indexes on tenant_id + frequently queried fields
   - Indexes on foreign keys and date ranges

2. **Eager Loading**
   - Load related models in service methods
   - Avoid N+1 queries in organizational chart

3. **Caching**
   - Consider caching organizational structure
   - Cache user permissions and roles

### Security Considerations

1. **Tenant Isolation**
   - Verify tenant_id in all queries
   - Prevent cross-tenant data access

2. **Role Management**
   - Ensure proper role assignment/removal
   - Validate position-role mappings

3. **Data Integrity**
   - Foreign key constraints
   - Prevent orphaned records

### Deliverables

1. **Database Migrations**
   - `create_position_categories_table.php`
   - `create_positions_table.php`
   - `add_position_fields_to_org_unit_users_table.php`
   - `add_special_fields_to_organization_units_table.php`

2. **Models**
   - `PositionCategory.php`
   - `Position.php`
   - Enhanced `OrgUnitUser.php`
   - Enhanced `User.php`
   - Enhanced `OrganizationUnit.php`

3. **Service**
   - `OrganizationStructureService.php`

4. **Tests**
   - Unit tests for all models
   - Feature tests for workflows
   - Integration tests for system behavior

5. **Documentation**
   - API documentation
   - Usage examples
   - Migration guide

### Success Criteria

- [ ] All database migrations run successfully
- [ ] All model relationships work correctly
- [ ] Position assignment automatically manages Spatie roles
- [ ] Historical tracking maintains complete audit trail
- [ ] Organizational chart generation works efficiently
- [ ] Multi-tenant isolation is maintained
- [ ] All tests pass
- [ ] Special units handle edge cases properly
- [ ] Performance requirements are met

---

## Example PHP code from Claude Code agent

```php
<?php

// Migration for position_categories table
Schema::create('position_categories', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id');
    $table->string('name');
    $table->string('slug')->nullable();
    $table->text('description')->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique(['tenant_id', 'name']);
    $table->index(['tenant_id', 'is_active']);
});

// Migration for positions table
Schema::create('positions', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id');
    $table->foreignId('organization_unit_id')->constrained()->onDelete('cascade');
    $table->foreignId('position_category_id')->constrained()->onDelete('restrict');
    $table->string('role_name'); // Links to Spatie role
    $table->string('name');
    $table->string('full_name')->virtualAs("CONCAT(name, ' - ', (SELECT name FROM organization_units WHERE id = organization_unit_id))");
    $table->text('description')->nullable();
    
    // Position flags
    $table->boolean('is_director')->default(false);
    $table->boolean('is_learning')->default(false);
    $table->boolean('is_temporary')->default(false);
    $table->boolean('is_contractor')->default(false);
    
    // Additional metadata
    $table->decimal('hourly_rate', 10, 2)->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique(['tenant_id', 'organization_unit_id', 'name']);
    $table->index(['tenant_id', 'is_active']);
    $table->index(['tenant_id', 'is_director']);
    $table->index(['tenant_id', 'is_learning']);
});

// Migration to add position to existing org_unit_users table
Schema::table('org_unit_users', function (Blueprint $table) {
    $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->boolean('is_primary')->default(false); // Primary position for users with multiple positions
    $table->text('notes')->nullable();
    
    $table->index(['tenant_id', 'position_id']);
    $table->index(['tenant_id', 'user_id', 'is_primary']);
});

// ====================================
// MODELS
// ====================================

class PositionCategory extends Model
{
    use HasTenant;
    
    protected $fillable = [
        'name', 'slug', 'description', 'sort_order', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function positions()
    {
        return $this->hasMany(Position::class);
    }
    
    public function activePositions()
    {
        return $this->positions()->where('is_active', true);
    }
    
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            Position::class,
            'position_category_id',
            'id',
            'id',
            'id'
        )->through('user_positions');
    }
    
    // Scope for common queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

class Position extends Model
{
    use HasTenant;
    
    protected $fillable = [
        'organization_unit_id', 'position_category_id', 'role_name', 'name',
        'description', 'is_director', 'is_learning', 'is_temporary', 'is_contractor',
        'hourly_rate', 'sort_order', 'is_active'
    ];
    
    protected $casts = [
        'is_director' => 'boolean',
        'is_learning' => 'boolean',
        'is_temporary' => 'boolean',
        'is_contractor' => 'boolean',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];
    
    protected $appends = ['full_name'];
    
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
    
    public function category()
    {
        return $this->belongsTo(PositionCategory::class, 'position_category_id');
    }
    
    public function orgUnitUsers()
    {
        return $this->hasMany(OrgUnitUser::class);
    }
    
    public function users()
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
    
    public function currentUsers()
    {
        return $this->users()
                    ->whereHas('orgUnitUsers', function($query) {
                        $query->active();
                    });
    }
    
    // Get the Spatie role
    public function getRole()
    {
        return Role::where('name', $this->role_name)->first();
    }
    
    // Virtual attribute for full name
    public function getFullNameAttribute()
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

class OrgUnitUser extends Model
{
    use HasTenant;
    
    protected $fillable = [
        'user_id', 'organization_unit_id', 'position_id', 'start_date', 'end_date', 'is_primary', 'notes'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
    
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    
    // Check if assignment is currently active
    public function isActive()
    {
        $startOk = $this->start_date ? $this->start_date <= now() : true;
        $endOk = $this->end_date ? $this->end_date >= now() : true;
        
        return $startOk && $endOk;
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where(function($q) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }
    
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
    
    public function scopeCurrent($query)
    {
        return $query->active();
    }
    
    public function scopeWithPosition($query)
    {
        return $query->whereNotNull('position_id');
    }
}

// Enhanced User model
class User extends Authenticatable
{
    use HasTenant, HasRoles; // Spatie trait
    
    public function orgUnitUsers()
    {
        return $this->hasMany(OrgUnitUser::class);
    }
    
    public function currentOrgUnitUsers()
    {
        return $this->orgUnitUsers()->active();
    }
    
    public function organizationUnits()
    {
        return $this->belongsToMany(OrganizationUnit::class, 'org_unit_users')
                    ->withPivot(['position_id', 'start_date', 'end_date', 'is_primary', 'notes'])
                    ->withTimestamps();
    }
    
    public function currentOrganizationUnits()
    {
        return $this->organizationUnits()
                    ->wherePivot('start_date', '<=', now())
                    ->where(function($query) {
                        $query->wherePivot('end_date', '>=', now())
                              ->orWherePivot('end_date', null);
                    });
    }
    
    public function positions()
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
    
    public function currentPositions()
    {
        return $this->positions()
                    ->whereHas('orgUnitUsers', function($query) {
                        $query->where('user_id', $this->id)->active();
                    });
    }
    
    public function primaryPosition()
    {
        $primaryOrgUnit = $this->currentOrgUnitUsers()->primary()->first();
        return $primaryOrgUnit?->position;
    }
    
    public function primaryOrganizationUnit()
    {
        $primaryOrgUnit = $this->currentOrgUnitUsers()->primary()->first();
        return $primaryOrgUnit?->organizationUnit;
    }
    
    // Assign user to organization unit with position
    public function assignToUnit(OrganizationUnit $unit, Position $position = null, array $options = [])
    {
        $options = array_merge([
            'start_date' => now()->toDateString(),
            'is_primary' => false,
            'notes' => null,
        ], $options);
        
        // Create org unit user assignment
        $orgUnitUser = $this->orgUnitUsers()->create([
            'organization_unit_id' => $unit->id,
            'position_id' => $position?->id,
            'start_date' => $options['start_date'],
            'is_primary' => $options['is_primary'],
            'notes' => $options['notes'],
        ]);
        
        // Assign role if position has one
        if ($position && $position->role_name) {
            $this->assignRole($position->role_name);
        }
        
        return $orgUnitUser;
    }
    
    // Remove user from organization unit
    public function removeFromUnit(OrganizationUnit $unit, $endDate = null)
    {
        $endDate = $endDate ?? now()->toDateString();
        
        $orgUnitUser = $this->orgUnitUsers()
                           ->where('organization_unit_id', $unit->id)
                           ->whereNull('end_date')
                           ->first();
        
        if ($orgUnitUser) {
            $orgUnitUser->end_date = $endDate;
            $orgUnitUser->save();
            
            // Remove role if no other positions have same role
            if ($orgUnitUser->position) {
                $hasOtherPositionsWithRole = $this->currentPositions()
                                                 ->where('role_name', $orgUnitUser->position->role_name)
                                                 ->where('id', '!=', $orgUnitUser->position->id)
                                                 ->exists();
                
                if (!$hasOtherPositionsWithRole) {
                    $this->removeRole($orgUnitUser->position->role_name);
                }
            }
        }
        
        return $orgUnitUser;
    }
    
    // Update position in organization unit
    public function updatePosition(OrganizationUnit $unit, Position $newPosition)
    {
        $orgUnitUser = $this->currentOrgUnitUsers()
                           ->where('organization_unit_id', $unit->id)
                           ->first();
        
        if ($orgUnitUser) {
            $oldPosition = $orgUnitUser->position;
            
            // Update position
            $orgUnitUser->position_id = $newPosition->id;
            $orgUnitUser->save();
            
            // Handle role changes
            if ($oldPosition && $oldPosition->role_name) {
                // Check if user has other positions with old role
                $hasOtherOldRoles = $this->currentPositions()
                                        ->where('role_name', $oldPosition->role_name)
                                        ->where('id', '!=', $oldPosition->id)
                                        ->exists();
                
                if (!$hasOtherOldRoles) {
                    $this->removeRole($oldPosition->role_name);
                }
            }
            
            // Assign new role
            if ($newPosition->role_name) {
                $this->assignRole($newPosition->role_name);
            }
        }
        
        return $orgUnitUser;
    }
    
    // Check if user is director
    public function isDirector()
    {
        return $this->currentPositions()->where('is_director', true)->exists();
    }
    
    // Check if user is in learning position
    public function isLearning()
    {
        return $this->currentPositions()->where('is_learning', true)->exists();
    }
    
    // Get user's position in specific unit
    public function getPositionInUnit(OrganizationUnit $unit)
    {
        $orgUnitUser = $this->currentOrgUnitUsers()
                           ->where('organization_unit_id', $unit->id)
                           ->first();
        
        return $orgUnitUser?->position;
    }
}

// Enhanced OrganizationUnit model
class OrganizationUnit extends Model
{
    use HasTenant;
    
    public function positions()
    {
        return $this->hasMany(Position::class);
    }
    
    public function activePositions()
    {
        return $this->positions()->active();
    }
    
    public function orgUnitUsers()
    {
        return $this->hasMany(OrgUnitUser::class);
    }
    
    public function currentOrgUnitUsers()
    {
        return $this->orgUnitUsers()->active();
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'org_unit_users')
                    ->withPivot(['position_id', 'start_date', 'end_date', 'is_primary', 'notes'])
                    ->withTimestamps();
    }
    
    public function currentUsers()
    {
        return $this->users()
                    ->wherePivot('start_date', '<=', now())
                    ->where(function($query) {
                        $query->wherePivot('end_date', '>=', now())
                              ->orWherePivot('end_date', null);
                    });
    }
    
    public function directors()
    {
        return $this->currentUsers()
                    ->whereHas('orgUnitUsers', function($query) {
                        $query->where('organization_unit_id', $this->id)
                              ->active()
                              ->whereHas('position', function($q) {
                                  $q->where('is_director', true);
                              });
                    });
    }
    
    public function getUsersWithPositions()
    {
        return $this->currentOrgUnitUsers()
                    ->with(['user', 'position.category'])
                    ->get()
                    ->map(function($orgUnitUser) {
                        return [
                            'user' => $orgUnitUser->user,
                            'position' => $orgUnitUser->position,
                            'category' => $orgUnitUser->position?->category,
                            'is_primary' => $orgUnitUser->is_primary,
                            'start_date' => $orgUnitUser->start_date,
                        ];
                    });
    }
}

// ====================================
// SERVICE CLASS
// ====================================

class OrganizationStructureService
{
    public function createSpecialUnits()
    {
        // Create special organization units
        $awaiting = OrganizationUnit::firstOrCreate([
            'name' => 'Awaiting Assignment',
            'slug' => 'awaiting-assignment',
        ], [
            'description' => 'Temporary unit for new users awaiting assignment',
            'is_active' => true,
            'is_special' => true, // Add this field to org units
        ]);
        
        $inactive = OrganizationUnit::firstOrCreate([
            'name' => 'Not Working Anymore',
            'slug' => 'not-working-anymore',
        ], [
            'description' => 'Unit for inactive users',
            'is_active' => false,
            'is_special' => true,
        ]);
        
        return [$awaiting, $inactive];
    }
    
    public function assignUserToPosition(User $user, OrganizationUnit $unit, Position $position, array $options = [])
    {
        $options = array_merge([
            'start_date' => now()->toDateString(),
            'is_primary' => false,
            'notes' => null,
        ], $options);
        
        DB::transaction(function() use ($user, $unit, $position, $options) {
            // Remove from awaiting if exists
            $this->removeFromAwaitingAssignment($user);
            
            // Assign to unit with position
            $user->assignToUnit($unit, $position, $options);
        });
    }
    
    public function removeFromAwaitingAssignment(User $user)
    {
        $awaitingUnit = OrganizationUnit::where('slug', 'awaiting-assignment')->first();
        if ($awaitingUnit) {
            $user->removeFromUnit($awaitingUnit);
        }
    }
    
    public function moveToInactive(User $user)
    {
        DB::transaction(function() use ($user) {
            // End all current assignments
            $user->currentOrgUnitUsers()->update(['end_date' => now()->toDateString()]);
            
            // Remove all current roles
            $user->syncRoles([]);
            
            // Move to inactive unit
            $inactiveUnit = OrganizationUnit::where('slug', 'not-working-anymore')->first();
            if ($inactiveUnit) {
                $user->assignToUnit($inactiveUnit, null, ['is_primary' => true]);
            }
        });
    }
    
    public function getOrganizationChart()
    {
        return OrganizationUnit::with([
            'positions.category',
            'currentOrgUnitUsers.user',
            'currentOrgUnitUsers.position'
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
    
    public function transferUser(User $user, OrganizationUnit $fromUnit, OrganizationUnit $toUnit, Position $newPosition = null)
    {
        DB::transaction(function() use ($user, $fromUnit, $toUnit, $newPosition) {
            // End assignment in old unit
            $user->removeFromUnit($fromUnit);
            
            // Assign to new unit
            $user->assignToUnit($toUnit, $newPosition, ['is_primary' => true]);
        });
    }
    
    public function getAllDirectors()
    {
        return User::whereHas('currentOrgUnitUsers', function($query) {
            $query->whereHas('position', function($q) {
                $q->where('is_director', true);
            });
        })->with(['currentOrgUnitUsers.organizationUnit', 'currentOrgUnitUsers.position'])->get();
    }
    
    public function getAllLearningPositions()
    {
        return User::whereHas('currentOrgUnitUsers', function($query) {
            $query->whereHas('position', function($q) {
                $q->where('is_learning', true);
            });
        })->with(['currentOrgUnitUsers.organizationUnit', 'currentOrgUnitUsers.position'])->get();
    }
} $user)
    {
        DB::transaction(function() use ($user) {
            // End all current positions
            $user->currentUserPositions()->update(['end_date' => now()->toDateString()]);
            
            // Move to inactive unit
            $inactiveUnit = OrganizationUnit::where('slug', 'not-working-anymore')->first();
            if ($inactiveUnit) {
                $inactivePosition = $inactiveUnit->positions()->first();
                if ($inactivePosition) {
                    $user->assignPosition($inactivePosition, now()->toDateString(), true);
                }
            }
        });
    }
    
    public function getOrganizationChart()
    {
        return OrganizationUnit::with([
            'positions.category',
            'positions.currentUsers'
        ])->get()->map(function($unit) {
            return [
                'unit' => $unit,
                'positions' => $unit->positions->map(function($position) {
                    return [
                        'position' => $position,
                        'users' => $position->currentUsers,
                        'category' => $position->category,
                    ];
                }),
            ];
        });
    }
}

// ====================================
// EXAMPLE USAGE
// ====================================

// Create categories
$directorCategory = PositionCategory::create([
    'name' => 'Director',
    'slug' => 'director',
    'sort_order' => 1,
]);

$employeeCategory = PositionCategory::create([
    'name' => 'Employee',
    'slug' => 'employee',
    'sort_order' => 2,
]);

// Create positions
$itDirector = Position::create([
    'organization_unit_id' => $itDepartment->id,
    'position_category_id' => $directorCategory->id,
    'role_name' => 'it-director',
    'name' => 'IT Director',
    'is_director' => true,
]);

$developer = Position::create([
    'organization_unit_id' => $itDepartment->id,
    'position_category_id' => $employeeCategory->id,
    'role_name' => 'developer',
    'name' => 'Senior Developer',
]);

// Assign user to organization unit with position
$service = new OrganizationStructureService();
$service->assignUserToPosition($user, $itDepartment, $itDirector, [
    'is_primary' => true,
    'start_date' => now()->toDateString(),
]);

// Alternative direct assignment
$user->assignToUnit($itDepartment, $developer, [
    'is_primary' => false,
    'notes' => 'Temporary assignment'
]);

// Update user's position within same unit
$user->updatePosition($itDepartment, $itDirector);

// Transfer user to different unit
$service->transferUser($user, $itDepartment, $hrDepartment, $hrManagerPosition);

// Query examples
$allDirectors = $service->getAllDirectors();

$learningUsers = $service->getAllLearningPositions();

// Get users in specific unit with their positions
$itUsers = $itDepartment->getUsersWithPositions();

// Get user's current position in a specific unit
$userPositionInIT = $user->getPositionInUnit($itDepartment);

// Check user status
$isDirector = $user->isDirector();
$isLearning = $user->isLearning();

// Get organization chart
$orgChart = $service->getOrganizationChart();

// Complex queries using OrgUnitUser
$directorsInIT = OrgUnitUser::active()
    ->where('organization_unit_id', $itDepartment->id)
    ->whereHas('position', function($query) {
        $query->where('is_director', true);
    })
    ->with(['user', 'position'])
    ->get();

$allActiveAssignments = OrgUnitUser::active()
    ->with(['user', 'organizationUnit', 'position.category'])
    ->get();

// Users without positions (in unit but no specific position)
$usersWithoutPositions = OrgUnitUser::active()
    ->whereNull('position_id')
    ->with(['user', 'organizationUnit'])
    ->get();
```
