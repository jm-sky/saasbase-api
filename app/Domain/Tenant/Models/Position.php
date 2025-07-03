<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Permission\Models\Role;

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
        'is_director'  => 'boolean',
        'is_learning'  => 'boolean',
        'is_temporary' => 'boolean',
        'is_active'    => 'boolean',
        'hourly_rate'  => 'decimal:2',
        'sort_order'   => 'integer',
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
            ->whereHas('orgUnitUsers', function ($query) {
                $query->active();
            })
        ;
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
