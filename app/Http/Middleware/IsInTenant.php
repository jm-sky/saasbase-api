<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsInTenant
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (!Auth::check() || !Auth::payload()?->get('tid')) {
            return response()->json([
                'message'        => 'Tenant context required',
                'actionRequired' => 'select-tenant',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
