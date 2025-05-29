<?php

namespace App\Services\MfLookup\Enums;

enum VatStatusEnum: string
{
    case ACTIVE   = 'Czynny';
    case EXEMPT   = 'Zwolniony';
    case INACTIVE = 'Nieczynny';
    case UNKNOWN  = 'Unknown'; // fallback when API gives unexpected value

    public static function fromString(?string $value): self
    {
        return match ($value) {
            'Czynny'    => self::ACTIVE,
            'Zwolniony' => self::EXEMPT,
            'Nieczynny' => self::INACTIVE,
            default     => self::UNKNOWN,
        };
    }
}
