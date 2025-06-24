<?php

namespace App\Domain\Exchanges\Enums;

enum ExchangeRateSource: string
{
    case NBP    = 'NBP';
    case Manual = 'Manual';
    // Add other sources as needed

    public function label(): string
    {
        return match ($this) {
            self::NBP    => 'Narodowy Bank Polski',
            self::Manual => 'Manual',
            // Add other labels as needed
        };
    }
}
