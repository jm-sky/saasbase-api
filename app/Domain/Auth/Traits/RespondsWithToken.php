<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

trait RespondsWithToken
{
    protected function tokenResponseData(string $token): array
    {
        return [
            'accessToken' => $token,
            'tokenType'   => 'bearer',
            'expiresIn'   => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    protected function getRefreshToken(User $user, ?string $tenantId)
    {
        return $tenantId
            ? JwtHelper::createTokenWithTenant($user, $tenantId)
            : JwtHelper::createRefreshToken($user);
    }

    protected function respondWithToken(string $token, ?User $user = null, ?string $tenantId = null, bool $remember = false): \Illuminate\Http\JsonResponse
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Create refresh token with the same tenant context as access token
        $refreshToken = $this->getRefreshToken($user, $tenantId);

        return response()
            ->json($this->tokenResponseData($token))
            ->withCookie($this->getRefreshTokenCookie($refreshToken, remember: $remember))
        ;
    }

    protected function getRefreshTokenCookie(string $refreshToken, bool $remember = false): Cookie
    {
        return cookie(
            'refresh_token',
            value: $refreshToken,
            minutes: $this->getRefreshTokenCookieTtl($remember),
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: 'Strict'
        );
    }

    protected function getRefreshTokenCookieTtl(bool $remember = false): int
    {
        if ($remember) {
            return 60 * 24 * 30; // 30 days
        }

        return 60 * 12; // 12 hours
    }
}
