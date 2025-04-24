<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Auth\Models\User;
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
                'last_name' => '',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(40)),
            ]
        );

        $token = auth()->login($user); // JWT

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
