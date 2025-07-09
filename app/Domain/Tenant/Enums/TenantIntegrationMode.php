<?php

namespace App\Domain\Tenant\Enums;

enum TenantIntegrationMode: string
{
    case Shared = 'shared';
    case Custom = 'custom';
}
