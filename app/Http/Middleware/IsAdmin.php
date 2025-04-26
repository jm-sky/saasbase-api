<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, \Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admins only.',
            ], Response::HTTP_FORBIDDEN); // zamiast 403
        }

        return $next($request);
    }
}
