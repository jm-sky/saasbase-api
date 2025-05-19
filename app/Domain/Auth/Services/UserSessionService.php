<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Enums\SessionType;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserSessionService
{
    public function createSession(User $user, Request $request, string $token): UserSession
    {
        $session                 = new UserSession();
        $session->user_id        = $user->id;
        $session->type           = SessionType::JWT;
        $session->token_id       = $token;
        $session->ip_address     = $request->ip();
        $session->user_agent     = $request->userAgent();
        $session->device_name    = $request->deviceName();
        $session->last_active_at = now();
        $session->expires_at     = now()->addMinutes(Config::get('jwt.refresh_ttl'));
        $session->save();

        return $session;
    }

    public function getCurrentSession(): ?UserSession
    {
        $user  = request()->user();
        $token = request()->bearerToken();

        if (!$token) {
            return null;
        }

        return $user->sessions()->whereNull('revoked_at')->where('token_id', $token)->first();
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
}
