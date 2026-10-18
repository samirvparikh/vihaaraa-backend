<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Storefront API endpoints are for active customer accounts; a disabled account loses access immediately. */
class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->isAdmin() || ! $user->is_active) {
            $user?->currentAccessToken()?->delete();

            return response()->json(['message' => 'Your session has ended. Please sign in again.'], 401);
        }

        return $next($request);
    }
}
