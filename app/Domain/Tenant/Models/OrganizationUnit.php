<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Contracts\AllocationDimensionInterface;
use App\Domain\Expense\Traits\HasAllocationDimensionInterface;
use App\Domain\Tenant\Enums\TechnicalOrganizationUnit;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

/**
 * @property string                            $id
 * @property ?string                           $tenant_id
 * @property ?string                           $parent_id
 * @property string                            $name
 * @property ?string                           $code
 * @property ?string                           $description
 * @property bool                              $is_active
 * @property bool                              $is_technical
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 * @property ?Tenant                           $tenant
 * @property ?OrganizationUnit                 $parent
 * @property Collection<int, OrganizationUnit> $children
 * @property Collection<int, User>             $users
 * @property Collection<int, OrgUnitUser>      $orgUnitUsers
 * @property Collection<int, OrgUnitUser>      $workflowMemberships
 * @property Collection<int, Position>         $positions
 * @property Collection<int, Position>         $activePositions
 */
class OrganizationUnit extends BaseModel implements AllocationDimensionInterface
{
    use IsGlobalOrBelongsToTenant;
    use HasAllocationDimensionInterface;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'code',
        'description',
        'is_active',
        'is_technical',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_technical' => 'boolean',
    ];

    protected $attributes = [
        'is_active'    => true,
        'is_technical' => false,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (OrganizationUnit $unit) {
            $unit->code = $unit->code ?? Str::slug($unit->name);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Legacy relationship - keep for backward compatibility
    public function orgUnitUsers(): HasMany
    {
        return $this->hasMany(OrgUnitUser::class);
    }

    // Legacy relationship - keep for backward compatibility
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'org_unit_user')
            ->withPivot(['role', 'workflow_role_level', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
        ;
    }

    // Legacy relationship - keep for backward compatibility
    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'org_unit_user')
            ->withPivot(['role', 'workflow_role_level', 'is_primary', 'valid_from', 'valid_until'])
            ->wherePivot('is_active', true)
            ->wherePivot('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now())
                ;
            })
            ->withTimestamps()
        ;
    }

    // Enhanced membership system for allocation workflows (using existing OrgUnitUser)
    public function workflowMemberships(): HasMany
    {
        return $this->hasMany(OrgUnitUser::class)->whereNotNull('workflow_role_level');
    }

    public function workflowMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'org_unit_user')
            ->withPivot(['role', 'workflow_role_level', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
            ->whereNotNull('org_unit_user.workflow_role_level')
        ;
    }

    public function getOwnersAttribute(): SupportCollection
    {
        // @phpstan-ignore-next-line
        return $this->workflowMemberships()
            ->where('workflow_role_level', UnitRoleLevel::UNIT_OWNER)
            ->active()
            ->with('user')
            ->get()
            ->pluck('user')
        ;
    }

    /**
     * Get display name with code prefix.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->code) {
            return "{$this->code} - {$this->name}";
        }

        return $this->name;
    }

    /**
     * Get hierarchical path for display.
     */
    public function getFullPathAttribute(): string
    {
        $path    = [$this->name];
        $current = $this->parent;

        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Check if this unit is a child of another unit.
     */
    public function isChildOf(self $unit): bool
    {
        $current = $this->parent;

        while ($current) {
            if ($current->id === $unit->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function activePositions(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->positions()->active();
    }

    public function getUsersWithPositions()
    {
        // @phpstan-ignore-next-line
        return $this->orgUnitUsers()
            ->active()
            ->with(['user', 'position.category'])
            ->get()
            ->map(function ($orgUnitUser) {
                return [
                    'user'       => $orgUnitUser->user,
                    'position'   => $orgUnitUser->position,
                    'category'   => $orgUnitUser->position?->category,
                    'is_primary' => $orgUnitUser->is_primary,
                    'valid_from' => $orgUnitUser->valid_from,
                    'role'       => $orgUnitUser->role,
                ];
            })
        ;
    }

    public function getDirectors()
    {
        // @phpstan-ignore-next-line
        return $this->orgUnitUsers()
            ->active()
            ->whereHas('position', function ($query) {
                $query->where('is_director', true);
            })
            ->with(['user', 'position'])
            ->get()
            ->pluck('user')
        ;
    }

    /**
     * Get only active organization unit users.
     *
     * @return HasMany<OrgUnitUser,OrganizationUnit>
     */
    public function activeOrgUnitUsers(): HasMany
    {
        /* @phpstan-ignore-next-line */
        return $this->orgUnitUsers()->active();
    }

    /**
     * Get only primary organization unit users.
     *
     * @return HasMany<OrgUnitUser,OrganizationUnit>
     */
    public function primaryOrgUnitUsers(): HasMany
    {
        /* @phpstan-ignore-next-line */
        return $this->orgUnitUsers()->primary();
    }

    /**
     * Scope to active records only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnical($query)
    {
        return $query->where('is_technical', true);
    }

    public function scopeUnassigned($query)
    {
        return $query->technical()->where('code', TechnicalOrganizationUnit::Unassigned->value);
    }

    public function scopeFormerEmployees($query)
    {
        return $query->technical()->where('code', TechnicalOrganizationUnit::FormerEmployees->value);
    }

    /**
     * Scope to root level units (no parent).
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
