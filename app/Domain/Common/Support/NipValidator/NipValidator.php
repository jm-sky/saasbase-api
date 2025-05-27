<?php

namespace App\Domain\Common\Support\NipValidator;

use App\Domain\Common\Support\NipValidator\Exceptions\InvalidNipException;

/**
 * @see https://github.com/mrcnpdlk/validator/blob/master/src/mrcnpdlk/Validator/Types/Nip.php
 */
class NipValidator
{
    public const NIP_LENGTH = 10;

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

    /**
     * @throws InvalidNipException
     */
    public static function validate(string $checkedValue): bool
    {
        $checkedValue = self::sanitize($checkedValue);

        if (self::NIP_LENGTH !== strlen($checkedValue) || '0000000000' === $checkedValue) {
            throw new InvalidNipException('Invalid NIP format. NIP must be 10 digits.');
        }

        $weights  = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $checkSum = static::getChecksum($checkedValue, $weights) % 10;

        if ($checkSum !== intval(substr($checkedValue, -1)) || 10 === $checkSum) {
            throw new InvalidNipException('Checksum Error', 1);
        }

        return true;
    }

    public static function getChecksum(string $checkedValue, array $weights, int $modulo = 11): int
    {
        $sum          = 0;
        $countWeights = count($weights);

        for ($i = 0; $i < $countWeights; ++$i) {
            $sum += $weights[$i] * intval($checkedValue[$i]);
        }

        return $sum % $modulo;
    }
}
