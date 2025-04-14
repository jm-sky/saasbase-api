<?php

namespace App\Domain\Projects\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\{BaseModel, Comment, Attachment};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string $owner_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read User $owner
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, ProjectUser> $projectUsers
 * @property-read Collection<int, ProjectRequiredSkill> $requiredSkills
 * @property-read Collection<int, Comment> $comments
 * @property-read Collection<int, Attachment> $attachments
 */
class Project extends BaseModel
{
    protected array $fillable = [
        'tenant_id',
        'name',
        'description',
        'status',
        'owner_id',
    ];

    protected array $casts = [
        'status' => 'string',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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
