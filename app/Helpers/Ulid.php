<?php

namespace App\Helpers;

use Symfony\Component\Uid\Ulid as SymfonyUlid;

class Ulid
{
    /**
     * Generates a deterministic ULID based on environment, APP_KEY and identifier.
     *
     * @param string|array        $keywords - identifier or array of identifiers
     * @param ?\DateTimeInterface $date     - optional date (default: 2025-01-01 UTC)
     *
     * @return string deterministic ULID (26 characters)
     */
    public static function deterministic(string|array $keywords, ?\DateTimeInterface $date = null): string
    {
        $env    = config('app.env', 'unknown');
        $appKey = env('APP_KEY', 'no-key');

        $keyword = is_array($keywords) ? implode('|', $keywords) : $keywords;
        $date ??= new \DateTimeImmutable('2025-01-01T00:00:00Z', new \DateTimeZone('UTC'));

        // Create a base ULID
        $ulid       = new SymfonyUlid();
        $ulidString = $ulid->toBase32();

        // Use the hash of our input to modify the random part
        $input = implode('|', [$env, $appKey, $keyword]);
        $hash  = sha1($input, true);

        // Take first 10 bytes of hash for the random part
        $randomPart = substr(bin2hex($hash), 0, 20);

        // Convert to base32 using Symfony's ULID format
        $randomPart = strtoupper(base_convert($randomPart, 16, 32));
        $randomPart = str_pad($randomPart, 16, '0', STR_PAD_LEFT);

        // Replace any invalid characters (I, L, O, U) with valid ones
        $randomPart = str_replace(['I', 'L', 'O', 'U'], ['1', '1', '0', 'V'], $randomPart);

        // Keep the timestamp part from the original ULID
        $timePart = substr($ulidString, 0, 10);

        // Combine parts
        $ulidString = $timePart . $randomPart;

        return SymfonyUlid::fromString($ulidString)->toBase32();
    }
}
