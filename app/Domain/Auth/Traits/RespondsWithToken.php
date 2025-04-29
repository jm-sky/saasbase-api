<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
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

    protected function respondWithToken(string $token, ?User $user = null): \Illuminate\Http\JsonResponse
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Check if current token has tenant context
        $payload  = JWTAuth::decode(JWTAuth::getToken());
        $tenantId = $payload->get('tid');

        // Create refresh token with the same tenant context as access token
        $refreshToken = $tenantId
            ? JwtHelper::createTokenWithTenant($user, $tenantId)
            : JwtHelper::createRefreshToken($user);

        return response()
            ->json($this->responseData($token))
            ->withCookie(cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 7, // 7 days
                '/',        // path
                null,       // domain
                true,      // secure
                true,      // httpOnly
                false,     // raw
                'Strict'   // SameSite
            ))
        ;
    }
}
