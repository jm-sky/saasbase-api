<?php

namespace App\Domain\Projects\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $user_id
 * @property string $project_role_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Project $project
 * @property-read User $user
 * @property-read ProjectRole $role
 */
class ProjectUser extends BaseModel
{
    protected array $fillable = [
        'project_id',
        'user_id',
        'project_role_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id');
    }
}
