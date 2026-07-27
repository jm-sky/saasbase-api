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
        try {
            // @phpstan-ignore-next-line
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            // The provider-side token exchange can fail for reasons outside our
            // control (code already used, expired, user denied consent, etc).
            // Surface it to the frontend instead of a raw 500.
            report($e);

            $url = config('app.frontend_url').'/oauth/callback?error=oauth_failed';

            return response()->redirectTo($url);
        }

        $providerUserId = $socialUser->getId();
        $email = $socialUser->getEmail();

        $oauthAccount = OAuthAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($oauthAccount) {
            /** @var User $user */
            $user = $oauthAccount->user;
        } else {
            /** @var ?User $existingUser */
            $existingUser = $email ? User::where('email', $email)->first() : null;

            if ($existingUser) {
                // Provider-verified email is proof of ownership (Google email_verified,
                // GitHub primary+verified email). Link and continue login — same
                // behaviour as gear-stack. Reject only when the provider did not
                // confirm the address, to avoid takeover via unverified claims.
                if (! $this->providerVerifiedEmail($provider, $socialUser)) {
                    $url = config('app.frontend_url').'/oauth/callback?error=account_exists';

                    return response()->redirectTo($url);
                }

                $user = $existingUser;
            } else {
                /** @var User $user */
                $user = User::create([
                    'first_name' => $this->extractFirstName($socialUser->getName(), $email),
                    'last_name' => $this->extractLastName($socialUser->getName()),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(40)),
                ]);
            }

            OAuthAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'email' => $email,
                'linked_at' => now(),
            ]);
        }

        $token = JwtHelper::createTokenWithoutTenant($user);

        $url = config('app.frontend_url').'/oauth/callback?jwtToken='.$token;

        return response()->redirectTo($url);
    }

    /**
     * Whether the OAuth provider confirmed ownership of the returned email.
     *
     * @param  \Laravel\Socialite\Contracts\User|\Laravel\Socialite\Two\User  $socialUser
     */
    private function providerVerifiedEmail(string $provider, object $socialUser): bool
    {
        // Socialite's GitHub driver only returns a primary+verified address.
        if ($provider === 'github') {
            return filled($socialUser->getEmail());
        }

        $raw = method_exists($socialUser, 'getRaw') ? $socialUser->getRaw() : ($socialUser->user ?? []);

        return (bool) ($raw['email_verified'] ?? $raw['verified_email'] ?? false);
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
