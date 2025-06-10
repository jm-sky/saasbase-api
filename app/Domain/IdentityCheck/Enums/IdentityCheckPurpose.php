<?php

namespace App\Domain\IdentityCheck\Enums;

enum IdentityCheckPurpose: string
{
    case Identity     = 'identity';
    case OfficialData = 'official_data';
    case Ownership    = 'ownership';
}
