<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Enums\SessionType;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserSessionService
{
    public function createSession(User $user, Request $request, string $token): UserSession
    {
        // @phpstan-ignore-next-line
        $tokenId = Arr::get(JWTAuth::getJWTProvider()->decode($token), 'jti');

        $session                 = new UserSession();
        $session->user_id        = $user->id;
        $session->type           = SessionType::JWT;
        $session->token_id       = $tokenId;
        $session->ip_address     = $request->ip();
        $session->user_agent     = $request->userAgent();
        $session->device_name    = $this->extractDeviceName($request->userAgent());
        $session->last_active_at = now();
        $session->expires_at     = now()->addMinutes(Config::get('jwt.refresh_ttl'));
        $session->save();

        return $session;
    }

    public function getCurrentSession(): ?UserSession
    {
        $user = request()->user();

        // Auth::payload() reads from the already-authenticated guard for this
        // request; the raw JWTAuth facade instead requires something to have
        // called parseToken()/getToken() on it first in the same request, or
        // it throws "A token is required" — a distinct, easy-to-miss state.
        $tokenId = Auth::payload()?->get('jti');

        if (!$tokenId) {
            return null;
        }

        // @phpstan-ignore-next-line
        return $user->sessions()->whereNull('revoked_at')->where('token_id', $tokenId)->first();
    }

    public function revokeCurrentSession(): void
    {
        $session = $this->getCurrentSession();

        if (!$session) {
            return;
        }

        $session->revoked_at = now();
        $session->save();
    }

    public function deleteCurrentSession(): void
    {
        $session = $this->getCurrentSession();

        if (!$session) {
            return;
        }

        $session->delete();
    }

    /**
     * Revoke a specific session belonging to the given user. Enforcement
     * happens in EnsureSessionNotRevoked, which rejects any request bearing
     * the revoked session's token.
     */
    public function revokeById(User $user, string $sessionId): bool
    {
        /** @var UserSession|null $session */
        $session = $user->sessions()->whereNull('revoked_at')->find($sessionId);

        if (!$session) {
            return false;
        }

        $session->revoked_at = now();
        $session->save();

        return true;
    }

    /**
     * Revoke every active session for the user except the one currently
     * making the request.
     */
    public function revokeAllExcept(User $user): int
    {
        $currentSession = $this->getCurrentSession();

        return $user->sessions()
            ->whereNull('revoked_at')
            ->when($currentSession, fn ($query) => $query->where('id', '!=', $currentSession->id))
            ->update(['revoked_at' => now()])
        ;
    }

    private function extractDeviceName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown Device';
        }

        // Extract browser name
        if (preg_match('/(?:Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)[\/\s](\d+)/i', $userAgent, $matches)) {
            $browser = $matches[0];
        } else {
            $browser = 'Unknown Browser';
        }

        // Extract OS name
        if (preg_match('/(?:Windows|Macintosh|Linux|Android|iOS|iPhone|iPad)[\/\s]?(\d+)?/i', $userAgent, $matches)) {
            $os = $matches[0];
        } else {
            $os = 'Unknown OS';
        }

        return sprintf('%s on %s', $browser, $os);
    }
}
