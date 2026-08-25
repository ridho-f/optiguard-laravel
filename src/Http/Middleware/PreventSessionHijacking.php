<?php

namespace OptiGuard\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OptiGuard\Laravel\Helpers\DeviceFingerprint;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionHijacking
{
    /**
     * Handle an incoming request.
     * Prevents session hijacking & cookie duplication across different IP addresses or browsers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('optiguard.enabled', true) || !config('optiguard.anti_hijacking.enabled', true)) {
            return $next($request);
        }

        // 1. Lewatkan rute yang dikecualikan
        $excludedRoutes = config('optiguard.anti_hijacking.excluded_routes', [
            'login', 'logout', 'register', 'password/*', 'two-factor-challenge', 'verify-2fa', 'api/optiguard/*'
        ]);

        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if (Auth::check()) {
            $clientIp = DeviceFingerprint::resolveClientIp($request);
            $userAgent = (string) $request->userAgent();
            $currentFingerprint = DeviceFingerprint::generate($request);

            $session = $request->session();

            // 2. Jika baru pertama kali login di sesi ini, catat sidik jari laptop asli
            if (!$session->has('_optiguard_fingerprint')) {
                $session->put('_optiguard_fingerprint', $currentFingerprint);
                $session->put('_optiguard_ip', $clientIp);
                $session->put('_optiguard_ua', $userAgent);
            } else {
                $savedFingerprint = $session->get('_optiguard_fingerprint');

                // 3. Terdeteksi request datang dari browser / device / IP berbeda!
                if ($savedFingerprint !== $currentFingerprint) {
                    $originalIp = $session->get('_optiguard_ip');
                    $originalUa = $session->get('_optiguard_ua');

                    Log::warning('🚨 [OPTIGUARD] Upaya Kloning Cookie / Session Hijacking Ditolak!', [
                        'user_id' => Auth::id(),
                        'user_email' => Auth::user()?->email,
                        'original_ip' => $originalIp,
                        'incoming_ip' => $clientIp,
                        'original_user_agent' => $originalUa,
                        'incoming_user_agent' => $userAgent,
                        'path' => $request->path(),
                    ]);

                    // Hancurkan session dan paksa logout
                    Auth::guard('web')->logout();
                    $session->invalidate();
                    $session->regenerateToken();

                    $alertMessage = config('optiguard.anti_hijacking.flash_message', 'Sesi login Anda telah dibatalkan otomatis oleh sistem keamanan karena terdeteksi upaya duplikasi cookie / perpindahan perangkat (Anti-Hijacking Protocol).');
                    $redirectRoute = config('optiguard.anti_hijacking.redirect_route', 'login');

                    $session->flash('error', $alertMessage);
                    $session->save();

                    // Tangani request Inertia SPA
                    if ($request->header('X-Inertia') && class_exists(\Inertia\Inertia::class)) {
                        return \Inertia\Inertia::location(route($redirectRoute));
                    }

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Sesi tidak valid. Terdeteksi upaya perpindahan cookie antar-perangkat.'
                        ], 401);
                    }

                    return redirect()->route($redirectRoute)->with('error', $alertMessage);
                }
            }
        }

        return $next($request);
    }
}
