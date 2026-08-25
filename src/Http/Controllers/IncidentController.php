<?php

namespace OptiGuard\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OptiGuard\Laravel\Helpers\DeviceFingerprint;

class IncidentController extends Controller
{
    /**
     * Handle incoming telemetry security incident from @ridhof_1/optiguard-security frontend.
     */
    public function report(Request $request): JsonResponse
    {
        if (!config('optiguard.enabled', true) || !config('optiguard.telemetry.enabled', true)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $type = $request->input('type', 'security_incident');
        $payload = $request->input('payload', []);
        $timestamp = $request->input('timestamp', now()->toIso8601String());

        $clientIp = DeviceFingerprint::resolveClientIp($request);
        $userAgent = (string) $request->userAgent();

        Log::channel(config('optiguard.telemetry.log_channel', 'daily'))->warning("🛡️ [OPTIGUARD TELEMETRY] Incident: {$type}", [
            'type' => $type,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'ip' => $clientIp,
            'user_agent' => $userAgent,
            'timestamp' => $timestamp,
            'payload' => $payload,
        ]);

        return response()->json([
            'status' => 'received',
            'incident_id' => bin2hex(random_bytes(8)),
        ], 200);
    }
}
