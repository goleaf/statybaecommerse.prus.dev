<?php

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\AttachCorrelationId;
use App\Services\TranslationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

require_once __DIR__ . '/../app/Support/filament_compat.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
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
        $middleware->prepend(AttachCorrelationId::class);
        $middleware->append(App\Http\Middleware\SetLocale::class);
        $middleware->append(App\Http\Middleware\SetFilamentLocale::class);
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
        $exceptions->render(function (DomainException $exception, Request $request) {
            $availableLocales = TranslationService::getAvailableLocales();
            $preferred = $request->getPreferredLanguage($availableLocales);
            $locale = is_string($preferred) && $preferred !== ''
                ? $preferred
                : (is_string(app()->getLocale()) && app()->getLocale() !== ''
                    ? app()->getLocale()
                    : TranslationService::getDefaultLocale());

            if (! in_array($locale, $availableLocales, true)) {
                $locale = TranslationService::getDefaultLocale();
            }

            app()->setLocale($locale);

            $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);

            $correlationId = $request->attributes->get('correlation_id')
                ?? (app()->bound('request_correlation_id')
                    ? (string) app()->make('request_correlation_id')
                    : Str::uuid()->toString());

            return response()->json([
                'code' => $exception->errorCode(),
                'message' => $message,
                'correlation_id' => $correlationId,
            ], $exception->status());
        });
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\LocaleServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
    ])
    ->create();
