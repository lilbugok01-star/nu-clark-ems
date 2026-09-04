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
        // Block direct access to hidden dotfiles, null bytes, and path traversal sequences
        $path = $request->path();
        $rawUri = $request->getRequestUri();
        if (
            preg_match('#(?:^|/)\.#', $path) || 
            stripos($path, '.htaccess') !== false || 
            stripos($path, '.env') !== false ||
            str_contains($rawUri, "\0") || 
            str_contains($rawUri, '%00') || 
            preg_match('#(?:\.\./|\.\.\\\\)#', $rawUri)
        ) {
            return response("404 Not Found\n", 404, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $response = $next($request);

        // 1. Strict-Transport-Security (HSTS) - Force HTTPS
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https' || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // 2. Content-Security-Policy (CSP) - Hardened Level 3 policy (Self-hosted fonts)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "font-src 'self' https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https://*.amazonaws.com https://*.railway.app; " .
               "connect-src 'self' https://cdn.jsdelivr.net; " .
               "form-action 'self'; " .
               "base-uri 'self'; " .
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
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        // 7. Cache-Control for sensitive / dynamic routes
        if ($request->isMethodSafe()) {
            if (!$response->headers->has('Cache-Control') || str_contains((string)$response->headers->get('Cache-Control'), 'private') || str_contains((string)$response->headers->get('Cache-Control'), 'no-cache')) {
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        }

        // 8. Minimize Redirect Body (prevents ZAP "Big Redirect" / sensitive leak heuristic)
        if ($response->isRedirection()) {
            $location = htmlspecialchars((string)$response->headers->get('Location'), ENT_QUOTES, 'UTF-8');
            $response->setContent('<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . $location . '" /></head></html>');
        }

        // 9. Enforce HttpOnly on XSRF-TOKEN cookie
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN' && !$cookie->isHttpOnly()) {
                $response->headers->setCookie(
                    new \Symfony\Component\HttpFoundation\Cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->isSecure(),
                        true, // HttpOnly enabled
                        $cookie->isRaw(),
                        $cookie->getSameSite(),
                        $cookie->isPartitioned()
                    )
                );
            }
        }

        // 10. Remove information-leaking headers
        if (!headers_sent()) {
            header_remove('X-Powered-By');
            header_remove('Server');
        }
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
