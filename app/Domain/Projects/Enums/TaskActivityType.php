<?php

namespace App\Domain\Projects\Enums;

enum TaskActivityType: string
{
    case Created        = 'task.created';
    case Updated        = 'task.updated';
    case Deleted        = 'task.deleted';
    case StatusChanged  = 'task.status.changed';
    case Assigned       = 'task.assigned';
    case Unassigned     = 'task.unassigned';
    case CommentAdded   = 'task.comment.added';
    case CommentUpdated = 'task.comment.updated';
    case CommentDeleted = 'task.comment.deleted';
}
