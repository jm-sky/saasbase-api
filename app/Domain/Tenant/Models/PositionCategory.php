<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
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
        'is_active'  => 'boolean',
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
            'id',
            'id',
            'id'
        );
    }

    /**
     * Get users who have positions in this category.
     */
    public function getUsersWithPositions()
    {
        return User::whereHas('orgUnitUsers.position', function ($query) {
            $query->where('position_category_id', $this->id);
        });
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
