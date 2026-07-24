<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHealthDetailsToken
{
    public function handle(Request $request, \Closure $next): Response
    {
        $expected = config('health.details_token');

        if (! is_string($expected) || $expected === '') {
            return $this->unauthorized();
        }

        $authorization = $request->header('Authorization', '');

        if (! str_starts_with($authorization, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = substr($authorization, 7);

        if (! hash_equals($expected, $token)) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    private function unauthorized(): Response
    {
        return response()->json([
            'message' => 'Invalid or missing health details token',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
