<?php

namespace App\Http\Middleware;

use App\Domain\Auth\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Rejects requests carrying a JWT whose backing UserSession row has been
 * revoked (see UserSessionService::revokeById/revokeAllExcept). UserSession
 * is otherwise pure tracking data with no bearing on the JWT guard itself,
 * so without this check "revoke this device" would only flip a database
 * column while the token kept working until its natural expiry.
 */
class EnsureSessionNotRevoked
{
    public function handle(Request $request, \Closure $next): Response
    {
        try {
            $tokenId = Auth::payload()?->get('jti');
        } catch (JWTException) {
            // This middleware always runs behind auth:api, which already
            // parses/validates the token — a request that got this far
            // without a parseable JWT is authenticated through some other
            // means (e.g. actingAs() in tests). Nothing to enforce here.
            return $next($request);
        }

        if ($tokenId && UserSession::query()->where('token_id', $tokenId)->whereNotNull('revoked_at')->exists()) {
            return response()->json([
                'message' => 'Session has been revoked.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
