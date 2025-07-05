<?php

namespace App\Domain\Utils\Enums;

enum RegistryConfirmationStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed  = 'failed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
