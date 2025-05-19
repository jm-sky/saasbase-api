<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Enums\SessionType;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserSessionService
{
    public function createSession(User $user, Request $request, string $token): UserSession
    {
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
        $user    = request()->user();
        $tokenId = JWTAuth::getPayload()->get('jti');

        if (!$tokenId) {
            return null;
        }

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
