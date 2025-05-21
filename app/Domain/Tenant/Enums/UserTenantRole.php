<?php

namespace App\Domain\Tenant\Enums;

enum UserTenantRole: string
{
    case Admin = 'admin';
    case User  = 'user';
}
