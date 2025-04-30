<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\AttachmentDTO;
use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Projects\Models\Task;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string  $id           UUID
 * @property string   $tenantId
 * @property string   $projectId
 * @property string   $title
 * @property ?string  $description
 * @property string   $status
 * @property string   $priority
 * @property ?string  $assignedToId
 * @property string   $createdById
 * @property ?string  $dueDate      ISO 8601 date
 * @property ?string  $createdAt    ISO 8601 timestamp
 * @property ?string  $updatedAt    ISO 8601 timestamp
 * @property ?string  $deletedAt    ISO 8601 timestamp
 * @property ?UserDTO $assignedTo
 * @property ?UserDTO $createdBy
 * @property ?array   $watchers
 * @property ?array   $comments
 * @property ?array   $attachments
 */
class TaskDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $projectId,
        public readonly string $title,
        public readonly string $status,
        public readonly string $priority,
        public readonly string $createdById,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $assignedToId = null,
        public readonly ?string $dueDate = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
        public ?UserDTO $assignedTo = null,
        public ?UserDTO $createdBy = null,
        public ?array $watchers = null,
        public ?array $comments = null,
        public ?array $attachments = null,
    ) {
    }

    public static function fromModel(Task $model): self
    {
        return new self(
            tenantId: $model->tenant_id,
            projectId: $model->project_id,
            title: $model->title,
            status: $model->status,
            priority: $model->priority,
            createdById: $model->created_by_id,
            id: $model->id,
            description: $model->description,
            assignedToId: $model->assigned_to_id,
            dueDate: $model->due_date?->toISOString(),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            assignedTo: $model->assignedTo ? UserDTO::fromModel($model->assignedTo) : null,
            createdBy: $model->createdBy ? UserDTO::fromModel($model->createdBy) : null,
            watchers: $model->watchers ? $model->watchers->map(fn ($watcher) => TaskWatcherDTO::fromModel($watcher))->toArray() : null,
            comments: $model->comments ? $model->comments->map(fn ($comment) => CommentDTO::fromModel($comment))->toArray() : null,
            attachments: $model->attachments ? $model->attachments->map(fn ($attachment) => AttachmentDTO::fromModel($attachment))->toArray() : null,
        );
    }
}
