<?php

namespace App\Domain\Utils\Enums;

use App\Traits\HasEnumValues;

enum RegistryConfirmationStatus: string
{
    use HasEnumValues;

    case Pending = 'pending';
    case Success = 'success';
    case Failed  = 'failed';
}
