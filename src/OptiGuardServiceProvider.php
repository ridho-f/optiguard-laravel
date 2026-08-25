<?php

namespace OptiGuard\Laravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use OptiGuard\Laravel\Http\Controllers\IncidentController;
use OptiGuard\Laravel\Http\Middleware\PreventSessionHijacking;
use OptiGuard\Laravel\Http\Middleware\SecurityHeaders;

class OptiGuardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/optiguard.php', 'optiguard'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router, Kernel $kernel): void
    {
        // 1. Publish Configuration & Views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/optiguard.php' => config_path('optiguard.php'),
            ], 'optiguard-config');
        }

        $this->loadViewsFrom(__DIR__ . '/Views', 'optiguard');

        // 2. Register Middleware Aliases
        $router->aliasMiddleware('optiguard.hijack', PreventSessionHijacking::class);
        $router->aliasMiddleware('optiguard.headers', SecurityHeaders::class);

        // 3. Register Telemetry Route
        if (config('optiguard.enabled', true) && config('optiguard.telemetry.enabled', true)) {
            $path = config('optiguard.telemetry.route_path', 'api/optiguard/incident');

            Route::post($path, [IncidentController::class, 'report'])
                ->middleware('api')
                ->name('optiguard.incident');
        }
    }
}
