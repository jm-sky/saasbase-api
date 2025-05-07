<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\Comment;
use App\Domain\Tenant\Concerns\BelongsToTenant;

class ProjectComment extends Comment
{
    use BelongsToTenant;
}
