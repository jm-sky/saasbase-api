<?php

namespace App\Domain\Auth\Traits;

trait RespondsWithToken
{
    protected function respondWithToken($token)
    {
        return response()
            ->json([
                'accessToken' => $token,
                'tokenType'   => 'bearer',
                'expiresIn'   => auth()->factory()->getTTL() * 60,
                'user'        => auth()->user(),
            ])
            ->withCookie(cookie(
                'refresh_token',
                $token,
                60 * 24 * 7, // 7 dni
                '/',         // ścieżka
                null,        // domena
                true,        // secure
                true,        // httpOnly
                false,       // raw
                'Strict'     // SameSite
            ))
        ;
    }
}
