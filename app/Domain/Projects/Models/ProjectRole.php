<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property ?string $description
 * @property ?array $permissions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Collection<int, ProjectUser> $projectUsers
 */
class ProjectRole extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }
}
