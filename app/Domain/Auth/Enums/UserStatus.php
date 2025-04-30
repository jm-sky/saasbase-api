<?php

namespace App\Domain\Auth\Enums;

enum UserStatus: string
{
    case ACTIVE  = 'active';
    case PENDING = 'pending';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE  => 'Active',
            self::PENDING => 'Pending',
            self::BLOCKED => 'Blocked',
        };
    }
}
