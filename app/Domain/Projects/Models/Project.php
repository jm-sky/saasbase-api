<?php

namespace App\Domain\Projects\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\{BaseModel, Comment, Attachment};
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany, BelongsToMany};
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property ?string $description
 * @property string $status
 * @property string $owner_id
 * @property Carbon $start_date
 * @property ?Carbon $end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read User $owner
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, ProjectUser> $projectUsers
 * @property-read Collection<int, ProjectRequiredSkill> $requiredSkills
 * @property-read Collection<int, Comment> $comments
 * @property-read Collection<int, Attachment> $attachments
 */
class Project extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'status',
        'owner_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'status' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withPivot(['role'])
            ->withTimestamps();
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
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }
}
