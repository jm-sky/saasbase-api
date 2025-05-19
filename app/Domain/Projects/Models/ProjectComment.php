<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\Comment;
use App\Domain\Tenant\Traits\BelongsToTenant;

class ProjectComment extends Comment
{
    use BelongsToTenant;
}
