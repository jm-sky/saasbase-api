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
 * - rem (remember): Whether the token is a remember token (0 or 1)
 */
class JwtHelper
{
    public static function getCustomClaims(User $user, bool $twoFactorPassed = false, ?string $tenantId = null, bool $remember = false): array
    {
        $payload = [
            'ev' => $user->hasVerifiedEmail() ? 1 : 0,
        ];

        if ($user->isTwoFactorEnabled()) {
            $payload['mfa'] = $twoFactorPassed ? 2 : 1;
        }

        if ($tenantId) {
            $payload['tid'] = $tenantId;
        }

        if ($remember) {
            $payload['rem'] = 1;
        }

        return array_merge(
            $user->getJWTCustomClaims(),
            $payload,
        );
    }

    public static function createTokenWithoutTenant(User $user, bool $twoFactorPassed = false): string
    {
        $customClaims = self::getCustomClaims($user, twoFactorPassed: $twoFactorPassed);

        return JWTAuth::claims($customClaims)->fromUser($user);
    }

    public static function createTokenWithTenant(User $user, string $tenantId, bool $twoFactorPassed = false): string
    {
        $customClaims = self::getCustomClaims($user, twoFactorPassed: $twoFactorPassed, tenantId: $tenantId);

        return JWTAuth::claims($customClaims)->fromUser($user);
    }

    public static function createRefreshToken(User $user, ?string $tenantId = null, bool $remember = false): string
    {
        $ttl = self::getRefreshTokenTTL($remember);

        $customClaims = self::getCustomClaims($user, tenantId: $tenantId, remember: $remember);

        return JWTAuth::factory()
            ->setTTL($ttl)
            ->claims($customClaims)
            ->fromUser($user)
        ;
    }

    public static function getRefreshTokenTTL(bool $remember = false): int
    {
        return $remember
            ? Config::get('jwt.refresh_ttl_remember')
            : Config::get('jwt.refresh_ttl');
    }
}
