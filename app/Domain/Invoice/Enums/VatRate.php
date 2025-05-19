<?php

namespace App\Domain\Invoice\Enums;

enum VatRate: string
{
    case VAT_23             = '23%';
    case VAT_8              = '8%';
    case VAT_5              = '5%';
    case VAT_0              = '0%';
    case VAT_EXEMPT         = 'zw';    // zwolnione
    case VAT_NOT_APPLICABLE = 'np'; // nie podlega

    public function label(): string
    {
        return match ($this) {
            self::VAT_23             => '23%',
            self::VAT_8              => '8%',
            self::VAT_5              => '5%',
            self::VAT_0              => '0%',
            self::VAT_EXEMPT         => 'Zwolnione',
            self::VAT_NOT_APPLICABLE => 'Nie podlega',
        };
    }

    public function rate(): float
    {
        return match ($this) {
            self::VAT_23 => 0.23,
            self::VAT_8  => 0.08,
            self::VAT_5  => 0.05,
            self::VAT_0  => 0.00,
            self::VAT_EXEMPT, self::VAT_NOT_APPLICABLE => 0.00,
        };
    }
}
