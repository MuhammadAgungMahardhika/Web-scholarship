<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    { // Check if the user is authenticated (logged in)
        if (Auth::check()) {
            // If the user is logged in, redirect them to the admin/LIMS dashboard
            // We use the intended() method for robustness, falling back to /admin
            return redirect()->intended('/scholarship');
        }
        return $next($request);
    }
}
