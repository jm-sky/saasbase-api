<?php

namespace App\Domain\Common\Support\RegonValidator;

use App\Domain\Common\Support\RegonValidator\Exceptions\InvalidRegonException;

class RegonValidator
{
    public const REGON_LENGTH = [9, 14];

    public static function sanitize(string $checkedValue): string
    {
        return preg_replace('/[^0-9]/', '', $checkedValue);
    }

    public static function sanitizeAndValidate(string $checkedValue): string
    {
        $checkedValue = self::sanitize($checkedValue);
        self::validate($checkedValue);

        return $checkedValue;
    }

    public static function validate(string $checkedValue): bool
    {
        $checkedValue = self::sanitize($checkedValue);

        if (!in_array(strlen($checkedValue), self::REGON_LENGTH)) {
            throw new InvalidRegonException('Invalid REGON format. REGON must be 9 or 14 digits.');
        }

        return true;
    }
}
