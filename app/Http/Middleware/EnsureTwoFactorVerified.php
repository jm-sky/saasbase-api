<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        $mfaStatus = Auth::payload()?->get('mfa');

        if (1 === $mfaStatus) {
            return response()->json([
                'message'        => 'Two-factor verification required.',
                'actionRequired' => 'verify-2fa',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
