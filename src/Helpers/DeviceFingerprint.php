<?php

namespace OptiGuard\Laravel\Helpers;

use Illuminate\Http\Request;

class DeviceFingerprint
{
    /**
     * Generate a cryptographic SHA256 fingerprint from the request's IP and User Agent.
     */
    public static function generate(Request $request): string
    {
        $ip = self::resolveClientIp($request);
        $userAgent = (string) $request->userAgent();

        return hash('sha256', $ip . '|' . $userAgent);
    }

    /**
     * Resolve the real client IP address, respecting Cloudflare & reverse proxy headers.
     */
    public static function resolveClientIp(Request $request): string
    {
        $rawIp = $request->header('CF-Pseudo-IPv4')
            ?? $request->header('CF-Connecting-IP')
            ?? $request->header('X-Real-IP')
            ?? $request->header('X-Forwarded-For')
            ?? $request->ip();

        if ($rawIp && str_contains($rawIp, ',')) {
            $parts = explode(',', $rawIp);
            $rawIp = trim($parts[0]);
        }

        return trim($rawIp ?? '127.0.0.1');
    }
}
