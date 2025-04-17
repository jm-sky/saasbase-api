<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var \App\Domain\Auth\Models\User $user */
            $user = Auth::user();

            // If tenant_id is provided in request and user has access to it
            if ($request->header('X-Tenant-ID') && $user->tenants()->where('id', $request->header('X-Tenant-ID'))->exists()) {
                session(['current_tenant_id' => $request->header('X-Tenant-ID')]);
            }
            // If no tenant is set in session but user has tenants, use the first one
            elseif (!session('current_tenant_id') && $user->tenants()->exists()) {
                session(['current_tenant_id' => $user->tenants()->first()->id]);
            }
        }

        return $next($request);
    }
}
