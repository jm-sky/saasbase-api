<?php

namespace App\Domain\Exchanges\Enums;

enum ExchangeRateSource: string
{
    case NBP = 'NBP';
    // Add other sources as needed

    public function label(): string
    {
        return match ($this) {
            self::NBP => 'Narodowy Bank Polski',
            // Add other labels as needed
        };
    }
}
