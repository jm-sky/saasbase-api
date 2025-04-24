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
                'first_name' => $this->extractFirstName($socialUser->getName()),
                'last_name' => $this->extractLastName($socialUser->getName()),
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

    private function extractFirstName(?string $fullName): string
    {
        if (!$fullName) return 'NoName';
        return explode(' ', trim($fullName))[0];
    }

    private function extractLastName(?string $fullName): string
    {
        if (!$fullName) return '';
        $parts = explode(' ', trim($fullName));
        array_shift($parts);
        return implode(' ', $parts); // everything after the first name
    }
}
