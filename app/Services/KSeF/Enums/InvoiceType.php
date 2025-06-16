<?php

namespace App\Services\KSeF\Enums;

enum InvoiceType: string
{
    case VAT     = 'VAT';
    case KOR     = 'KOR';
    case ZAL     = 'ZAL';
    case ROZ     = 'ROZ';
    case UPR     = 'UPR';
    case KOR_ZAL = 'KOR_ZAL';
    case KOR_ROZ = 'KOR_ROZ';

    public function description(): string
    {
        return match ($this) {
            self::VAT     => 'VAT Invoice',
            self::KOR     => 'Correction Invoice',
            self::ZAL     => 'Advance Invoice',
            self::ROZ     => 'Settlement Invoice',
            self::UPR     => 'Simplified Invoice',
            self::KOR_ZAL => 'Advance Correction Invoice',
            self::KOR_ROZ => 'Settlement Correction Invoice',
        };
    }
}
