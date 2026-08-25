# OptiGuard Laravel 🛡️

> **Enterprise Backend Security Suite for Laravel Applications**  
> Seamlessly pairs with [@ridhof_1/optiguard-security](https://npmjs.com/package/@ridhof_1/optiguard-security) to deliver zero-trust session integrity, device fingerprinting, and security telemetry.

---

## 🚀 Key Features

* 🛡️ **Anti-Session Hijacking**: Binds sessions to device signatures (IP + Browser). Cloned cookies are rejected and destroyed instantly.
* 🔒 **HTTP Security Headers**: Automatic injection of `X-Frame-Options`, `Content-Security-Policy`, `X-Content-Type-Options`, and `Referrer-Policy`.
* 📡 **Incident Telemetry Endpoint**: Receives real-time DevTools detection & tamper events from OptiGuard Frontend.
* ⚡ **Inertia.js & SPA Ready**: Automatically performs clean SPA hard reloads without redirect loops.
* ⚙️ **Fully Configurable**: Publishable config with customizable routes, error messages, and bypass rules.

---

## 📦 Installation

```bash
composer require ridhof/optiguard-laravel
```

Publish configuration file:
```bash
php artisan vendor:publish --tag=optiguard-config
```

---

## ⚙️ Configuration (`config/optiguard.php`)

```php
return [
    'enabled' => env('OPTIGUARD_ENABLED', true),

    'anti_hijacking' => [
        'enabled' => true,
        'redirect_route' => 'login',
        'flash_message' => 'Sesi login Anda telah dibatalkan otomatis oleh sistem keamanan karena terdeteksi upaya duplikasi cookie (Anti-Hijacking).',
        'excluded_routes' => [
            'login', 'logout', 'register', 'password/*', 'verify-2fa', 'api/optiguard/*'
        ],
    ],

    'security_headers' => [
        'enabled' => true,
        'headers' => [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ],
    ],

    'telemetry' => [
        'enabled' => true,
        'route_path' => 'api/optiguard/incident',
        'log_channel' => 'daily',
    ],
];
```

---

## 🛠️ Usage in Laravel 11 / 12 / 13

In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \OptiGuard\Laravel\Http\Middleware\PreventSessionHijacking::class,
        \OptiGuard\Laravel\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

---

## 📄 License
MIT License © 2026 Ridho.
