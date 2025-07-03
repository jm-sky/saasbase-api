<?php

namespace App\Domain\Tenant\Enums;

enum UnitRoleLevel: string
{
    case UNIT_MEMBER = 'unit-member';
    case UNIT_DEPUTY = 'unit-deputy';
    case UNIT_OWNER  = 'unit-owner';
    case UNIT_ADMIN  = 'unit-admin';

    public function label(): string
    {
        return match ($this) {
            self::UNIT_MEMBER => 'Member',
            self::UNIT_DEPUTY => 'Deputy Manager',
            self::UNIT_OWNER  => 'Manager/Owner',
            self::UNIT_ADMIN  => 'Administrator',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::UNIT_MEMBER => 'Członek',
            self::UNIT_DEPUTY => 'Zastępca',
            self::UNIT_OWNER  => 'Kierownik',
            self::UNIT_ADMIN  => 'Administrator',
        };
    }

    public function getHierarchyLevel(): int
    {
        return match ($this) {
            self::UNIT_MEMBER => 1,
            self::UNIT_DEPUTY => 2,
            self::UNIT_OWNER  => 3,
            self::UNIT_ADMIN  => 4,
        };
    }

    public function canApproveFor(UnitRoleLevel $requestorLevel): bool
    {
        return $this->getHierarchyLevel() > $requestorLevel->getHierarchyLevel();
    }
}
