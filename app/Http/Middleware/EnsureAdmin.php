<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Only active admin users may use the admin panel. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAdmin() || ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            return redirect()->route('admin.login')->withErrors(['email' => 'Please sign in with an admin account.']);
        }

        return $next($request);
    }
}
