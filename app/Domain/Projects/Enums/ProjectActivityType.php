<?php

namespace App\Domain\Projects\Enums;

enum ProjectActivityType: string
{
    case Created       = 'project.created';
    case Updated       = 'project.updated';
    case Deleted       = 'project.deleted';
    case StatusChanged = 'project.status.changed';
    case MemberAdded   = 'project.member.added';
    case MemberRemoved = 'project.member.removed';
    case TaskAdded     = 'project.task.added';
    case TaskRemoved   = 'project.task.removed';
}
