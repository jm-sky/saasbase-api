<?php

namespace App\Domain\Common\Enums;

class DatabaseColumnLength
{
    public const EMAIL    = 254;      // RFC 5321 maximum length
    public const PHONE    = 20;       // International phone number with formatting
    public const COUNTRY  = 2;      // ISO 3166-1 alpha-2 country code
    public const WEBSITE  = 255;    // Standard URL length
    public const VAT_ID   = 20;      // European VAT identification number
    public const TAX_ID   = 20;      // Global tax identification number
    public const REGON    = 20;       // Business registration number
    public const NAME     = 255;       // Standard name length
    public const COLOR    = 7;        // Hex color code (#RRGGBB)
    public const SLUG     = 255;       // URL-friendly string
    public const PASSWORD = 255;   // Hashed password length
    public const TOKEN    = 100;      // Authentication token length
    public const CURRENCY = 3;     // ISO 4217 currency code
    public const IBAN     = 34;        // International Bank Account Number ISO 13616
    public const SWIFT    = 11;       // SWIFT/BIC code ISO 9362
}
