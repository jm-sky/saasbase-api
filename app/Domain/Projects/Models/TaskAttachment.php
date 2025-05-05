<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\Attachment;
use App\Domain\Tenant\Concerns\BelongsToTenant;

class TaskAttachment extends Attachment
{
    use BelongsToTenant;
}
