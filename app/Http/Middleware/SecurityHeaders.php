<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Strict-Transport-Security (HSTS) - Force HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // 2. Content-Security-Policy (CSP) - Whitelist trusted sources
        // Note: Allowing 'unsafe-inline' for now to ensure compatibility with existing scripts/styles
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https:; " .
               "frame-ancestors 'none'; " .
               "object-src 'none';";
        
        $response->headers->set('Content-Security-Policy', $csp);

        // 3. X-Frame-Options - Prevent Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // 4. X-Content-Type-Options - Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 5. Referrer-Policy - Protect user privacy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 6. Permissions-Policy - Limit access to browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 7. Remove information-leaking headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
