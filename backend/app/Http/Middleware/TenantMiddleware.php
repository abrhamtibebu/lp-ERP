<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tenant context is set via authenticated user's tenant_id
        // This middleware ensures user has a tenant_id
        if (auth()->check() && !auth()->user()->tenant_id) {
            return response()->json(['message' => 'User must be associated with a tenant'], 403);
        }

        return $next($request);
    }
}

