<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\Comment;
use App\Domain\Tenant\Concerns\BelongsToTenant;

class TaskComment extends Comment
{
    use BelongsToTenant;
}
