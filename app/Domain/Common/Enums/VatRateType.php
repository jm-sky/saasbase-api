<?php

namespace App\Domain\Common\Enums;

enum VatRateType: string
{
    case PERCENTAGE     = 'percentage';       // e.g. 23%, 8%, 5%
    case ZERO_PERCENT   = 'zero_percent';     // 0%
    case EXEMPT         = 'exempt';           // "ZW" — Exempt from VAT
    case NOT_SUBJECT    = 'not_subject';      // "NP" — Not subject to VAT
    case REVERSE_CHARGE = 'reverse_charge';   // "OO" — Reverse charge
    case MARGIN_SCHEME  = 'margin_scheme';    // "MR_T", "MR_UZ", etc. — Margin-based VAT
}
