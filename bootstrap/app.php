<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/system-settings.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(App\Http\Middleware\SetLocale::class);
        $middleware->append(App\Http\Middleware\SetFilamentLocale::class);
        // Detect and persist customer sales zone for storefront
        $middleware->append(App\Http\Middleware\ZoneDetector::class);
        // Handle user impersonation for admin support
        $middleware->append(App\Http\Middleware\HandleImpersonation::class);
        // Register Spatie permission middlewares (Laravel 11+/12 style)
        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'permissions' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize' => App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\LocaleServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
    ])
    ->create();
