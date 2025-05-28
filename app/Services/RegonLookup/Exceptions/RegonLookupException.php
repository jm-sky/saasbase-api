<?php

namespace App\Services\RegonLookup\Exceptions;

class RegonLookupException extends \Exception
{
    public static function invalidNip(string $nip): self
    {
        return new self("Invalid NIP: {$nip}");
    }

    public static function invalidRegon(string $regon): self
    {
        return new self("Invalid REGON: {$regon}");
    }

    public static function noDataFound(string $identifier): self
    {
        return new self("No data found for identifier: {$identifier}");
    }

    public static function apiError(string $message): self
    {
        return new self("REGON API error: {$message}");
    }

    public static function authenticationFailed(): self
    {
        return new self('Failed to authenticate with REGON API');
    }

    public static function sessionExpired(): self
    {
        return new self('REGON API session has expired');
    }

    public static function invalidResponse(): self
    {
        return new self('Invalid response from REGON API');
    }
}
