<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'first_name' => $socialUser->getName() ?? 'NoName',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(40)),
            ]
        );

        $token = auth()->login($user); // JWT

        return $this->respondWithToken($token);
    }

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
            ));
    }
}
