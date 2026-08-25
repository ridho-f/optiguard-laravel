<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OptiGuard Master Switch
    |--------------------------------------------------------------------------
    |
    | Enable or disable OptiGuard backend security protection globally.
    |
    */
    'enabled' => env('OPTIGUARD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Anti-Session Hijacking & Cookie Theft Defense
    |--------------------------------------------------------------------------
    |
    | Binds user session to their initial device signature (IP + User Agent).
    | Any request attempting to use the session cookie from a different
    | device or network will be immediately rejected and logged.
    |
    */
    'anti_hijacking' => [
        'enabled' => env('OPTIGUARD_ANTI_HIJACKING', true),

        // Route to redirect when an unauthorized cookie copy attempt is detected
        'redirect_route' => 'login',

        // Flash message shown on login page
        'flash_message' => 'Sesi login Anda telah dibatalkan otomatis oleh sistem keamanan karena terdeteksi upaya duplikasi cookie / perpindahan perangkat (Anti-Hijacking Protocol).',

        // Routes to bypass from session hijacking check
        'excluded_routes' => [
            'login',
            'logout',
            'register',
            'password/*',
            'two-factor-challenge',
            'verify-2fa',
            'api/optiguard/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Security Headers
    |--------------------------------------------------------------------------
    |
    | Injects industry-standard security headers to defend against clickjacking,
    | MIME-type sniffing, cross-site scripting, and credential leaks.
    |
    */
    'security_headers' => [
        'enabled' => env('OPTIGUARD_SECURITY_HEADERS', true),

        'headers' => [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Incident Telemetry Endpoint
    |--------------------------------------------------------------------------
    |
    | Receives real-time security breach beacons from @ridhof_1/optiguard-security
    | frontend (DevTools detection, print attempts, tamper events).
    |
    */
    'telemetry' => [
        'enabled' => env('OPTIGUARD_TELEMETRY', true),
        'route_path' => 'api/optiguard/incident',
        'log_channel' => env('OPTIGUARD_LOG_CHANNEL', 'daily'),
    ],
];
