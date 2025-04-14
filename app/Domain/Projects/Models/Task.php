<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\{Comment, Attachment};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $title
 * @property ?string $description
 * @property string $status
 * @property string $priority
 * @property ?string $assigned_to_id
 * @property string $created_by_id
 * @property ?Carbon $due_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Project $project
 * @property-read ?User $assignedTo
 * @property-read User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaskWatcher> $watchers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attachment> $attachments
 */
class Task extends BaseModel
{
    protected array $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to_id',
        'created_by_id',
        'due_date',
    ];

    protected array $casts = [
        'status' => 'string',
        'priority' => 'string',
        'due_date' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(TaskWatcher::class);
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
