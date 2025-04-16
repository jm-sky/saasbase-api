<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\{CommentDTO, AttachmentDTO};
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $projectId
 * @property string $title
 * @property ?string $description
 * @property string $status
 * @property string $priority
 * @property ?string $assignedToId
 * @property string $createdById
 * @property ?string $dueDate
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $assignedTo
 * @property ?UserDTO $createdBy
 * @property ?array $watchers
 * @property ?array $comments
 * @property ?array $attachments
 */
class TaskDTO extends Data
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $title,
        public readonly string $status,
        public readonly string $priority,
        public readonly string $createdById,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $assignedToId = null,
        public readonly ?string $dueDate = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $assignedTo = null,
        public ?UserDTO $createdBy = null,
        public ?array $watchers = null,
        public ?array $comments = null,
        public ?array $attachments = null,
    ) {}
}
