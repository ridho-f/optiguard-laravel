<?php

namespace OptiGuard\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Injects HTTP security headers into the outgoing response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('optiguard.enabled', true) || !config('optiguard.security_headers.enabled', true)) {
            return $response;
        }

        $headers = config('optiguard.security_headers.headers', [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);

        foreach ($headers as $header => $value) {
            if ($value !== null && $value !== '') {
                $response->headers->set($header, $value, false);
            }
        }

        return $response;
    }
}
