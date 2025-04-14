<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use App\Domain\Common\DTOs\{CommentDto, AttachmentDto};
use Carbon\Carbon;

class TaskDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $project_id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $priority,
        public readonly ?string $assigned_to_id,
        public readonly string $created_by_id,
        public readonly ?Carbon $due_date,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $assignedTo = null,
        public readonly ?UserDto $createdBy = null,
        public readonly ?array $watchers = null,
        public readonly ?array $comments = null,
        public readonly ?array $attachments = null,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\Task $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            project_id: $model->project_id,
            title: $model->title,
            description: $model->description,
            status: $model->status,
            priority: $model->priority,
            assigned_to_id: $model->assigned_to_id,
            created_by_id: $model->created_by_id,
            due_date: $model->due_date,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            assignedTo: $withRelations && $model->relationLoaded('assignedTo')
                ? UserDto::fromModel($model->assignedTo)
                : null,
            createdBy: $withRelations && $model->relationLoaded('createdBy')
                ? UserDto::fromModel($model->createdBy)
                : null,
            watchers: $withRelations && $model->relationLoaded('watchers')
                ? $model->watchers->map(fn($watcher) => TaskWatcherDto::fromModel($watcher, true))->toArray()
                : null,
            comments: $withRelations && $model->relationLoaded('comments')
                ? $model->comments->map(fn($comment) => CommentDto::fromModel($comment, true))->toArray()
                : null,
            attachments: $withRelations && $model->relationLoaded('attachments')
                ? $model->attachments->map(fn($attachment) => AttachmentDto::fromModel($attachment))->toArray()
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to_id' => $this->assigned_to_id,
            'created_by_id' => $this->created_by_id,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'assigned_to' => $this->assignedTo?->toArray(),
            'created_by' => $this->createdBy?->toArray(),
            'watchers' => $this->watchers?->map(fn($watcher) => $watcher->toArray())->toArray(),
            'comments' => $this->comments?->map(fn($comment) => $comment->toArray())->toArray(),
            'attachments' => $this->attachments?->map(fn($attachment) => $attachment->toArray())->toArray(),
        ];
    }
}
