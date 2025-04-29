<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * JWT payload claims.
 *
 * Standard JWT claims (from Tymon/JWTAuth):
 * - iss (issuer): The issuer of the token
 * - iat (issued at): When the token was issued (Unix timestamp)
 * - exp (expiration): When the token expires (Unix timestamp)
 * - nbf (not before): When the token starts being valid (Unix timestamp)
 * - sub (subject): The subject of the token (user ID)
 * - jti (JWT ID): Unique identifier for the token
 *
 * Custom claims:
 * - ev (email verified): Whether user's email is verified (0 or 1)
 * - mfa (multi-factor auth): Multi-factor authentication status
 *       1: MFA is enabled but not passed
 *       2: MFA is enabled and passed
 * - tid (tenant ID): UUID of the current tenant context (only present in tenant-scoped tokens)
 */
class JwtHelper
{
    // 90 days in seconds: 90 * 24 * 60 * 60 = 7,776,000
    public const REFRESH_TOKEN_TTL = 90 * 24 * 60 * 60; // 90 days

    public static function getDefaultPayload(User $user, bool $twoFactorPassed = false): array
    {
        $payload = [
            'ev' => $user->hasVerifiedEmail() ? 1 : 0,
        ];

        if ($user->isTwoFactorEnabled()) {
            $payload['mfa'] = $twoFactorPassed ? 2 : 1;
        }

        return $payload;
    }

    public static function createTokenWithoutTenant(User $user, bool $twoFactorPassed = false): string
    {
        $payload = self::getDefaultPayload($user, $twoFactorPassed);

        return JWTAuth::fromUser($user, $payload);
    }

    public static function createTokenWithTenant(User $user, string $tenantId, bool $twoFactorPassed = false): string
    {
        $payload = array_merge(self::getDefaultPayload($user, $twoFactorPassed), [
            'tid' => $tenantId,
        ]);

        return JWTAuth::fromUser($user, $payload);
    }

    public static function createRefreshToken(User $user): string
    {
        $ttl = self::getRefreshTokenTTL();
        Config::set('jwt.ttl', $ttl);
        $payload = self::getDefaultPayload($user);

        $token = JWTAuth::fromUser($user, $payload);
        Config::set('jwt.ttl', config('jwt.ttl')); // Restore original TTL

        return $token;
    }

    public static function getRefreshTokenTTL(): int
    {
        return Config::get('jwt.refresh_ttl', self::REFRESH_TOKEN_TTL);
    }

    public static function generateToken(User $user, ?string $tenantId = null, bool $twoFactorPassed = false, bool $isRefreshToken = false): string
    {
        if ($isRefreshToken) {
            return self::createRefreshToken($user);
        }

        if ($tenantId) {
            return self::createTokenWithTenant($user, $tenantId, $twoFactorPassed);
        }

        return self::createTokenWithoutTenant($user, $twoFactorPassed);
    }
}
