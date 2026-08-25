# OptiGuard Laravel 🛡️

[![Latest Version](https://img.shields.io/github/v/release/ridho-f/optiguard-laravel?style=flat-square&color=indigo)](https://github.com/ridho-f/optiguard-laravel/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue.svg?style=flat-square)](https://php.net)
[![Laravel Framework](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-red.svg?style=flat-square)](https://laravel.com)

> **Enterprise Backend Security Suite for Laravel Applications**  
> Seamlessly pairs with [@ridhof_1/optiguard-security](https://npmjs.com/package/@ridhof_1/optiguard-security) to deliver zero-trust session integrity, device fingerprinting, anti-cookie theft, and real-time security telemetry.

---

## 📑 Daftar Isi
- [Fitur Utama](#-fitur-utama)
- [Instalasi](#-instalasi)
- [Panduan Penggunaan (Quick Start)](#-panduan-penggunaan-quick-start)
  - [Laravel 11 / 12 / 13](#laravel-11--12--13-bootstrapappphp)
  - [Laravel 10](#laravel-10-apphttpkernelphp)
- [Konfigurasi Lengkap (`config/optiguard.php`)](#-konfigurasi-lengkap)
- [Integrasi Full-Stack dengan OptiGuard Frontend](#-integrasi-full-stack-dengan-frontend)
- [Penjelasan Fitur Keamanan](#-penjelasan-fitur-keamanan)
  - [1. Anti-Session Hijacking & Cookie Theft](#1-anti-session-hijacking--cookie-theft)
  - [2. HTTP Security Headers](#2-http-security-headers)
  - [3. Incident Telemetry Receiver](#3-incident-telemetry-receiver)
- [Troubleshooting & FAQ](#-troubleshooting--faq)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

* 🛡️ **Anti-Session Hijacking & Cookie Theft Defense**: Mengikat sesi login secara kriptografis (*SHA256*) ke identitas perangkat (`IP + User-Agent`). Begitu cookie dicuri / di-copy ke laptop atau browser lain, sistem langsung membatalkan sesi dan menolak akses seketika.
* 🔒 **HTTP Security Headers Otomatis**: Injeksi header keamanan standar industri (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection: 1; mode=block`, `Referrer-Policy: strict-origin-when-cross-origin`).
* 📡 **Incident Telemetry Endpoint**: Endpoint bawaan `POST /api/optiguard/incident` siap pakai untuk menerima laporan real-time dari frontend (DevTools dibuka, upaya print/copy, manipulasi DOM).
* ⚡ **Inertia.js & Single Page Application (SPA) Ready**: Menangani pengalihan sesi menggunakan `Inertia::location()` untuk mencegah *infinite redirect loop*.
* ⚙️ **Publishable & Fully Configurable**: Konfigurasi lengkap yang dapat diatur via file `.env` maupun `config/optiguard.php`.

---

## 📦 Instalasi

Install package via Composer:

```bash
composer require ridhof/optiguard-laravel
```

Publish file konfigurasi ke proyek Anda:

```bash
php artisan vendor:publish --tag=optiguard-config
```

File konfigurasi baru akan dibuat di `config/optiguard.php`.

---

## 🚀 Panduan Penggunaan (Quick Start)

### Laravel 11 / 12 / 13 (`bootstrap/app.php`)

Daftarkan middleware OptiGuard di dalam pipeline `web`:

```php
// bootstrap/app.php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan middleware OptiGuard pada web stack
        $middleware->web(append: [
            \OptiGuard\Laravel\Http\Middleware\PreventSessionHijacking::class,
            \OptiGuard\Laravel\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->create();
```

---

### Laravel 10 (`app/Http/Kernel.php`)

Tambahkan middleware ke dalam grup `$middlewareGroups['web']`:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... middleware Laravel bawaan ...
        \OptiGuard\Laravel\Http\Middleware\PreventSessionHijacking::class,
        \OptiGuard\Laravel\Http\Middleware\SecurityHeaders::class,
    ],
];
```

---

## ⚙️ Konfigurasi Lengkap

File `config/optiguard.php` memungkinkan kustomisasi penuh:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | OptiGuard Master Switch
    |--------------------------------------------------------------------------
    */
    'enabled' => env('OPTIGUARD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Anti-Session Hijacking & Cookie Theft Defense
    |--------------------------------------------------------------------------
    */
    'anti_hijacking' => [
        'enabled' => env('OPTIGUARD_ANTI_HIJACKING', true),

        // Rute tujuan redirect saat terdeteksi kloning cookie
        'redirect_route' => 'login',

        // Pesan flash error yang dikirim ke halaman login
        'flash_message' => 'Sesi login Anda telah dibatalkan otomatis oleh sistem keamanan karena terdeteksi upaya duplikasi cookie / perpindahan perangkat (Anti-Hijacking Protocol).',

        // Rute publik yang dikecualikan dari pengecekan
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
    */
    'telemetry' => [
        'enabled' => env('OPTIGUARD_TELEMETRY', true),
        'route_path' => 'api/optiguard/incident',
        'log_channel' => env('OPTIGUARD_LOG_CHANNEL', 'daily'),
    ],
];
```

---

## 🌐 Integrasi Full-Stack dengan Frontend

OptiGuard Laravel bekerja berpasangan secara sempurna dengan package frontend **[@ridhof_1/optiguard-security](https://npmjs.com/package/@ridhof_1/optiguard-security)**.

### 1. Install Frontend Package
```bash
npm install @ridhof_1/optiguard-security
```

### 2. Inisialisasi di Frontend (`resources/js/app.tsx` / `main.ts`)
```typescript
import { initSecurityProtection } from '@ridhof_1/optiguard-security';

initSecurityProtection({
    disableInDev: false,
    redirectBehavior: 'logout',
    privacyBlur: {
        enabled: true,
        blurAmount: '16px',
        overlayTitle: 'OptiGuard Privacy Shield',
        overlaySubtitle: 'Tampilan disembunyikan untuk menjaga kerahasiaan data.',
    },
    idleLock: {
        enabled: true,
        timeout: 15 * 60 * 1000, // 15 menit
        action: 'lockscreen',
    },
    blockPrint: true,
    contentProtection: {
        blockTextSelection: true,
        blockCopy: true,
        blockCut: true,
        blockDragDrop: true,
    },
    wipeStorageOnDetect: true,
    
    // Hubungkan Telemetry ke Endpoint Backend OptiGuard Laravel:
    telemetry: {
        endpoint: '/api/optiguard/incident',
    },
});
```

---

## 🛡️ Penjelasan Fitur Keamanan

### 1. Anti-Session Hijacking & Cookie Theft
* **Bagaimana Cara Kerjanya?**
  1. Saat pengguna pertama kali login di perangkat A, server membuat sidik jari:
     $$\text{Signature} = \text{SHA256}(\text{IP Client} + \text{User-Agent Browser})$$
  2. Jika string cookie (`laravel_session`) dicuri atau di-copy ke laptop/browser lain (di mana IP atau User-Agent berbeda).
  3. Server mendeteksi perbedaan sidik jari, seketika memanggil `Auth::logout()` dan `$session->invalidate()`, serta mencatat log insiden lengkap.

### 2. HTTP Security Headers
* **Proteksi Clickjacking**: Mencegah website Anda dimuat di dalam `<iframe>` website berbahaya.
* **MIME Sniffing Prevention**: Memaksa browser mematuhi tipe konten `Content-Type`.
* **XSS Protection**: Memblokir script injection berbahaya pada browser lama.

### 3. Incident Telemetry Receiver
* Menerima payload insiden dari browser dan mencatatnya ke `storage/logs/laravel.log`:
```json
{
  "type": "devtools_detected",
  "user_id": "019fc128-dd08-73e7-af77-38a9409d5bb0",
  "user_email": "admin@example.com",
  "ip": "192.168.1.50",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
  "timestamp": "2026-08-26T00:00:00Z"
}
```

---

## ❓ Troubleshooting & FAQ

**Q: Mengapa saya ter-logout saat menguji mode responsif DevTools (misal ganti ke Pixel 9)?**  
*A: Saat Anda beralih ke emulator Pixel 9 di DevTools, browser mengubah User-Agent menjadi Linux Android. OptiGuard mendeteksi ini sebagai perpindahan perangkat (kloning cookie) dan mengamankan akun Anda.*

**Q: Apakah kompatibel dengan Cloudflare & Load Balancer?**  
*A: Ya! `DeviceFingerprint::resolveClientIp()` secara otomatis membaca header `CF-Connecting-IP`, `X-Real-IP`, dan `X-Forwarded-For`.*

---

## 📄 Lisensi
Package ini dirilis di bawah lisensi open-source **MIT License**.  
Dibuat dengan ❤️ oleh **Ridho** (2026).
