## Task: Implement Global & Tenant-Scoped Roles and Permissions (UUID, Tenant-Aware)

### Goal
Set up roles and permissions using the `spatie/laravel-permission` package in a multi-tenant Laravel API app. The system must support both **global** and **tenant-scoped** roles and permissions. UUIDs are used across all models.

---

### Steps

#### 1. Install the Package

    composer require spatie/laravel-permission

---

#### 2. Publish Config and Migrations

    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

---

#### 3. Modify Migrations for UUID + Tenancy

- Rename published migration files to run **after** the tenants table.
- In each table (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`), apply:

Example for `roles`:

    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('guard_name');
    $table->foreignUuid('tenant_id')->nullable()->constrained()->onDelete('cascade');

Repeat for:

- `permissions`
- `model_has_roles` (add `tenant_id`)
- `model_has_permissions` (add `tenant_id`)

Use `foreignUuid()` throughout.

---

#### 4. Create Permissions and Roles Seeder

Create `RolesAndPermissionsSeeder` and invoke it in `DatabaseSeeder`.

Permission name pattern: `{model}.{action}` written kebab-case.

Create **permissions** (`view`, `manage`) for models:

- Contractor (includes related financial/partner models)
- Project
- Task
- Product

Use `guard_name = 'api'` and `tenant_id = null` for global permissions.

Create **global roles** with permissions:

- `Admin`: All permissions
- `Owner`: Full tenant permissions
- `FinancialManager`: Can view/manage invoices, products, contractors
- `ProjectManager`: Can manage projects and all tasks
- `ProjectMember`: Can view and **edit** tasks assigned to them

Assign roles appropriate permissions using `syncPermissions`.

---

#### 5. Run Fresh Migrations and Seed

    php artisan migrate:fresh --seed

---

#### 6. Create Custom Role & Permission Models

Namespace: `App\Domain\Rights`

Role.php:

    namespace App\Domain\Rights;

    use Illuminate\Database\Eloquent\Concerns\HasUuids;
    use Spatie\Permission\Models\Role as SpatieRole;
    use App\Domain\Tenants\Traits\IsGlobalOrBelongsToTenant ;

    class Role extends SpatieRole
    {
        use IsGlobalOrBelongsToTenant ;
        use HasUuids;

        protected $fillable = ['name', 'guard_name', 'tenant_id'];
    }

Same structure for `Permission`.

Update `config/permission.php`:

    'models' => [
        'role' => App\Domain\Rights\Role::class,
        'permission' => App\Domain\Rights\Permission::class,
    ],
    'guard_name' => 'api',

---

#### 7. Add Scope and Trait for Global/Tenant Filtering

**Scope**: `app/Domain/Tenants/Scopes/GlobalOrCurrentTenantScope.php`

    namespace App\Domain\Tenants\Scopes;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Scope;

    class GlobalOrCurrentTenantScope implements Scope
    {
        protected ?string $tenantId;

        public function __construct(?string $tenantId)
        {
            $this->tenantId = $tenantId;
        }

        public function apply(Builder $builder, Model $model)
        {
            $builder->where(function ($query) {
                $query->whereNull('tenant_id')
                      ->orWhere('tenant_id', $this->tenantId);
            });
        }
    }

**Trait**: `app/Domain/Rights/Traits/IsGlobalOrBelongsToTenant .php`

    namespace App\Domain\Rights\Traits;

    use App\Domain\Rights\Scopes\GlobalOrCurrentTenantScope;

    trait IsGlobalOrBelongsToTenant 
    {
        protected static function bootHasGlobalOrTenantScope()
        {
            /** @var ?\App\Domain\Auth\Models\User $user */
            $user     = auth()->user();
            $tenantId = $user?->getTenantId() ?? \App\Domain\Tenant\Models\Tenant::$BYPASSED_TENANT_ID;

            static::addGlobalScope(new GlobalOrCurrentTenantScope($tenantId));
        }
    }

---

#### 8. Extend User DTO with Roles and Permissions
- Add `roles: string[]` and `permissions: string[]` to `UserDto`
- Use `user->getRoleNames()` and `user->getAllPermissions()->pluck('name')`
- Add to `/me` endpoint response
- Example:
  {
    id: '...',
    name: '...',
    roles: ['Owner'],
    permissions: ['project.manage', 'task.view']
  }

#### 9. Create RolesController with REST Actions
- Endpoint base: `/api/v1/roles`
- Controller: `App\Domain\Rights\Controllers\RoleController`
- Actions:
  - `index()` – return all roles (global and tenant) using `GlobalOrCurrentTenantScope` scope
  - `store()` – allow creating tenant-scoped roles
  - `update(Role $role)` – update name or permissions (only if role belongs to current tenant)
  - `destroy(Role $role)` – delete (only if role belongs to current tenant)
- Use policies or manual checks to prevent modifying global roles
- Apply `auth:api` middleware
- Return each role with (but use DTO to make props camel case):
  ```json
  {
    "id": "uuid",
    "name": "ProjectManager",
    "permissions": ["project.manage", "task.view"],
    "tenant_id": "uuid or null"
  }

---

### Notes

- Use Laravel’s `HasUuids` trait for models to avoid manual UUID setup
- Use `'api'` guard (JWT-based)
- Namespace: `App\Domain\Rights` (recommended for domain clarity)

---

### Acceptance Criteria

- [ ] Migrations renamed, use UUIDs, include `tenant_id`
- [ ] Roles and permissions created with global scope
- [ ] Seeder populates roles and permissions correctly
- [ ] Models use `HasUuids` and are correctly namespaced
- [ ] Scope filters roles/permissions by tenant or globally
- [ ] `ProjectMember` role includes ability to edit tasks
