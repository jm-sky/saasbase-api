<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Traits\RespondsWithToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    use RespondsWithToken;

    public function redirect(string $provider): JsonResponse
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): JsonResponse
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        /** @var User $user */
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'first_name'        => $this->extractFirstName($socialUser->getName(), $socialUser->getEmail()),
                'last_name'         => $this->extractLastName($socialUser->getName()),
                'email'             => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'password'          => bcrypt(Str::random(40)),
            ]
        );

        $token = JwtHelper::createTokenWithoutTenant($user);

        return $this->respondWithToken($token, $user);
    }

    private function extractFirstName(?string $fullName, ?string $fallback = null): string
    {
        if (empty(trim($fullName))) {
            return $fallback ? explode('@', $fallback)[0] : 'OAuth';
        }

        $parts = preg_split('/\s+/', trim($fullName));

        return $parts[0] ?? 'OAuth';
    }

    private function extractLastName(?string $fullName): string
    {
        if (empty(trim($fullName))) {
            return '';
        }

        $parts = preg_split('/\s+/', trim($fullName));
        array_shift($parts); // Remove the first name

        return count($parts) > 0 ? implode(' ', $parts) : '';
    }
}
