<?php

namespace App\Domain\Rights\Enums;

enum RoleName: string
{
    case Admin            = 'Admin';
    case Owner            = 'Owner';
    case Manager          = 'Manager';
    case FinancialManager = 'FinancialManager';
    case ProjectManager   = 'ProjectManager';
    case ProjectMember    = 'ProjectMember';
    case User             = 'User';

    public static function fromCaseInsensitive(string $value): static
    {
        return self::tryFrom(strtolower($value)) ?? self::User;
    }
}
