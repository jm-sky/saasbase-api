<?php

namespace App\Domain\Projects\Models;

use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string      $id
 * @property string      $project_id
 * @property string      $user_id
 * @property string      $project_role_id
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Project     $project
 * @property User        $user
 * @property ProjectRole $role
 */
class ProjectUser extends Pivot
{
    use HasUlids;

    protected $fillable = [
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
