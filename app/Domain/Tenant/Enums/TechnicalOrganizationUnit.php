<?php

namespace App\Domain\Tenant\Enums;

enum TechnicalOrganizationUnit: string
{
    case Unassigned      = 'unassigned';
    case FormerEmployees = 'former-employees';

    public function getName(): string
    {
        return match ($this) {
            self::Unassigned      => 'Unassigned',
            self::FormerEmployees => 'Former Employees',
        };
    }
}
