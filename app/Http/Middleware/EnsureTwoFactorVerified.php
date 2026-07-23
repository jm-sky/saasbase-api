<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Rejects requests authenticated with a token issued before two-factor
 * verification completed (JWT claim mfa === 1). Login only ever issues
 * mfa=1 when the user has 2FA enabled; TwoFactorAuthController::verify()
 * upgrades it to mfa=2 after a valid code. Users without 2FA enabled have
 * no 'mfa' claim at all and are unaffected.
 */
class EnsureTwoFactorVerified
{
    public function handle(Request $request, \Closure $next): Response
    {
        try {
            // @phpstan-ignore-next-line payload() is provided by the JWTAuth guard, not declared on the base Auth facade
            $mfaStatus = Auth::payload()?->get('mfa');
        } catch (JWTException) {
            // This middleware always runs behind auth:api, which already
            // parses/validates the token — a request that got this far
            // without a parseable JWT is authenticated through some other
            // means (e.g. actingAs() in tests). Nothing to enforce here.
            return $next($request);
        }

        if (1 === $mfaStatus) {
            return response()->json([
                'message'        => 'Two-factor verification required.',
                'actionRequired' => 'verify-2fa',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
