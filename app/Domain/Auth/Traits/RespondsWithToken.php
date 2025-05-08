<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use Illuminate\Http\JsonResponse;
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

    protected function respondWithToken(string $token, ?User $user = null, ?string $tenantId = null, bool $remember = false): JsonResponse
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Create refresh token with the same tenant context as access token
        $refreshToken = JwtHelper::createRefreshToken($user, tenantId: $tenantId, remember: $remember);

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
            minutes: $this->getRefreshTokenCookieTTL($remember),
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: 'Strict'
        );
    }

    protected function getRefreshTokenCookieTTL(bool $remember = false): int
    {
        return JwtHelper::getRefreshTokenTTL($remember);
    }
}
