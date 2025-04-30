<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsActive
{
    public function handle(Request $request, \Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isActive()) {
            return response()->json([
                'message' => 'Account is not active.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
