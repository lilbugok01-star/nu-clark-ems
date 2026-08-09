<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedCustom
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only students are required to go through email verification in this system
            if ($user->isStudent() && is_null($user->email_verified_at)) {
                // Allow access to verification routes, logout, and essential assets only
                $allowedPaths = ['verify-email', 'verify-email/*', 'logout'];
                $isAllowed = false;
                
                foreach ($allowedPaths as $path) {
                    if ($request->is($path)) {
                        $isAllowed = true;
                        break;
                    }
                }
                
                if (!$isAllowed && !$request->routeIs('logout')) {
                    return redirect()->route('verification.notice');
                }
            }
        }

        return $next($request);
    }
}
