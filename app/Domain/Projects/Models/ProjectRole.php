<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
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
    protected array $fillable = [
        'tenant_id',
        'name',
        'description',
        'permissions',
    ];

    protected array $casts = [
        'permissions' => 'array',
    ];

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }
}
