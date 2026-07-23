<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\OAuthAccount;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Traits\RespondsWithToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    use RespondsWithToken;

    public function redirect(string $provider): RedirectResponse
    {
        // @phpstan-ignore-next-line
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        // @phpstan-ignore-next-line
        $socialUser = Socialite::driver($provider)->stateless()->user();

        $providerUserId = $socialUser->getId();
        $email          = $socialUser->getEmail();

        $oauthAccount = OAuthAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first()
        ;

        if ($oauthAccount) {
            /** @var User $user */
            $user = $oauthAccount->user;
        } else {
            // An account with this email may already exist from local
            // registration or another OAuth provider. Logging in as it here
            // without any proof of ownership beyond "the OAuth provider says
            // so" would let anyone who controls that address on this
            // provider take over a pre-existing account. Require a fresh
            // identity to link instead of silently attaching to one.
            if ($email && User::where('email', $email)->exists()) {
                $url = config('app.frontend_url') . '/oauth/callback?error=account_exists';

                return response()->redirectTo($url);
            }

            /** @var User $user */
            $user = User::create([
                'first_name'        => $this->extractFirstName($socialUser->getName(), $email),
                'last_name'         => $this->extractLastName($socialUser->getName()),
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => bcrypt(Str::random(40)),
            ]);

            OAuthAccount::create([
                'user_id'          => $user->id,
                'provider'         => $provider,
                'provider_user_id' => $providerUserId,
                'email'            => $email,
            ]);
        }

        $token = JwtHelper::createTokenWithoutTenant($user);

        $url = config('app.frontend_url') . '/oauth/callback?jwtToken=' . $token;

        return response()->redirectTo($url);
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
