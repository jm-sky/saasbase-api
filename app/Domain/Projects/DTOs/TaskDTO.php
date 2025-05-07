<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\AttachmentDTO;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Projects\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Task>
 *
 * @property ?string  $id           UUID
 * @property string   $tenantId
 * @property string   $projectId
 * @property string   $title
 * @property ?string  $description
 * @property string   $status
 * @property string   $priority
 * @property ?string  $assignedToId
 * @property string   $createdById
 * @property ?Carbon  $dueDate      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $createdAt    Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt    Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt    Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO $assignedTo
 * @property ?UserDTO $createdBy
 * @property ?array   $watchers
 * @property ?array   $comments
 * @property ?array   $attachments
 */
class TaskDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $tenantId,
        public readonly string $projectId,
        public readonly string $status,
        public readonly string $priority,
        public readonly string $createdById,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $assignedToId = null,
        public readonly ?Carbon $dueDate = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?UserDTO $assignedTo = null,
        public ?UserDTO $createdBy = null,
        public ?array $watchers = null,
        public ?array $comments = null,
        public ?array $attachments = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Task $model */
        return new static(
            title: $model->title,
            tenantId: $model->tenant_id,
            projectId: $model->project_id,
            status: $model->status,
            priority: $model->priority,
            createdById: $model->created_by_id,
            id: $model->id,
            description: $model->description,
            assignedToId: $model->assigned_to_id,
            dueDate: $model->due_date,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            assignedTo: $model->assignedTo ? UserDTO::fromModel($model->assignedTo) : null,
            createdBy: $model->createdBy ? UserDTO::fromModel($model->createdBy) : null,
            watchers: $model->watchers?->map(fn ($watcher) => UserDTO::fromModel($watcher))->toArray(),
            comments: $model->comments?->map(fn ($comment) => CommentDTO::fromModel($comment))->toArray(),
            attachments: $model->attachments?->map(fn ($attachment) => AttachmentDTO::fromModel($attachment))->toArray(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            title: $data['title'],
            tenantId: $data['tenant_id'],
            projectId: $data['project_id'],
            status: $data['status'],
            priority: $data['priority'],
            createdById: $data['created_by_id'],
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            assignedToId: $data['assigned_to_id'] ?? null,
            dueDate: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            assignedTo: isset($data['assigned_to']) ? UserDTO::fromArray($data['assigned_to']) : null,
            createdBy: isset($data['created_by']) ? UserDTO::fromArray($data['created_by']) : null,
            watchers: isset($data['watchers']) ? array_map(fn ($watcher) => UserDTO::fromArray($watcher), $data['watchers']) : null,
            comments: isset($data['comments']) ? array_map(fn ($comment) => CommentDTO::fromArray($comment), $data['comments']) : null,
            attachments: isset($data['attachments']) ? array_map(fn ($attachment) => AttachmentDTO::fromArray($attachment), $data['attachments']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'projectId'   => $this->projectId,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'assignedToId'=> $this->assignedToId,
            'createdById' => $this->createdById,
            'dueDate'     => $this->dueDate?->toIso8601String(),
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
            'assignedTo'  => $this->assignedTo?->toArray(),
            'createdBy'   => $this->createdBy?->toArray(),
            'attachments' => $this->attachments?->map(fn (AttachmentDTO $dto) => $dto->toArray())->all(),
            'comments'    => $this->comments?->map(fn (CommentDTO $dto) => $dto->toArray())->all(),
            'watchers'    => $this->watchers?->map(fn (TaskWatcherDTO $dto) => $dto->toArray())->all(),
        ];
    }
}
