<?php

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\AttachCorrelationId;
use App\Http\Middleware\SecurityHeaders;
use App\Services\TranslationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require_once __DIR__.'/../app/Support/filament_compat.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/system-settings.php'));
            // Load admin routes
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            // Load reports routes
            Route::middleware('web')
                ->group(base_path('routes/reports.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(App\Http\Middleware\AddSecurityHeaders::class);
        $middleware->append(App\Http\Middleware\SetLocale::class);
        $middleware->append(App\Http\Middleware\SetFilamentLocale::class);
        // Handle user impersonation for admin support
        $middleware->append(App\Http\Middleware\HandleImpersonation::class);
        $middleware->append(SecurityHeaders::class);
        // Register Spatie permission middlewares (Laravel 11+/12 style)
        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'permissions' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize' => App\Http\Middleware\SetLocale::class,
            'abilities' => Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiErrorResponse::fromThrowable($exception, $request);
        });
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\ApiServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\LocaleServiceProvider::class,
        App\Providers\SecurityServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
        Laravel\Sanctum\SanctumServiceProvider::class,
    ])
    ->create();
