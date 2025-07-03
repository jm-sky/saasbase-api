<?php

namespace App\Domain\Approval\Enums;

enum ApproverType: string
{
    case USER              = 'user';                        // Specific user ID
    case UNIT_ROLE         = 'unit_role';              // Unit role level (unit-owner, unit-deputy, etc.)
    case SYSTEM_PERMISSION = 'system_permission'; // Spatie permission name

    public function label(): string
    {
        return match ($this) {
            self::USER              => 'Specific User',
            self::UNIT_ROLE         => 'Organization Unit Role',
            self::SYSTEM_PERMISSION => 'System Permission',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::USER              => 'Konkretny Użytkownik',
            self::UNIT_ROLE         => 'Rola w Jednostce Organizacyjnej',
            self::SYSTEM_PERMISSION => 'Uprawnienie Systemowe',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::USER              => 'A specific user will be assigned to approve',
            self::UNIT_ROLE         => 'Users with a specific role in an organizational unit will approve',
            self::SYSTEM_PERMISSION => 'Users with a specific system permission will approve',
        };
    }

    public function descriptionPL(): string
    {
        return match ($this) {
            self::USER              => 'Konkretny użytkownik zostanie przypisany do zatwierdzenia',
            self::UNIT_ROLE         => 'Użytkownicy z określoną rolą w jednostce organizacyjnej będą zatwierdzać',
            self::SYSTEM_PERMISSION => 'Użytkownicy z określonym uprawnieniem systemowym będą zatwierdzać',
        };
    }
}
