<?php

namespace App\Domain\Projects\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string                                $id
 * @property string                                $tenant_id
 * @property string                                $name
 * @property ?string                               $description
 * @property string                                $status_id
 * @property string                                $owner_id
 * @property Carbon                                $start_date
 * @property ?Carbon                               $end_date
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 * @property ?Carbon                               $deleted_at
 * @property ProjectStatus                         $status
 * @property User                                  $owner
 * @property Collection<int, User>                 $users
 * @property Collection<int, Task>                 $tasks
 * @property Collection<int, ProjectUser>          $projectUsers
 * @property Collection<int, ProjectRequiredSkill> $requiredSkills
 * @property Collection<int, ProjectComment>       $comments
 */
class Project extends BaseModel implements HasMedia
{
    use BelongsToTenant;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'status_id',
        'owner_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }

    public function requiredSkills(): HasMany
    {
        return $this->hasMany(ProjectRequiredSkill::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ProjectComment::class, 'commentable');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
