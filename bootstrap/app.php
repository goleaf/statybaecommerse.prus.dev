<?php

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\AttachCorrelationId;
use App\Http\Middleware\AddSecurityHeaders;
use App\Providers\SecurityServiceProvider;
use App\Services\TranslationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
        $middleware->prepend(AttachCorrelationId::class);
        $middleware->append(App\Http\Middleware\SetLocale::class);
        $middleware->append(App\Http\Middleware\SetFilamentLocale::class);
        // Handle user impersonation for admin support
        $middleware->append(App\Http\Middleware\HandleImpersonation::class);
        $middleware->append(AddSecurityHeaders::class);
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
            $currentLocale = app()->getLocale();
            $locale = is_string($preferred) && $preferred !== ''
                ? $preferred
                : ($currentLocale !== ''
                    ? $currentLocale
                    : TranslationService::getDefaultLocale());

            if (! in_array($locale, $availableLocales, true)) {
                $locale = TranslationService::getDefaultLocale();
            }

            app()->setLocale($locale);
            app()->instance('request_locale', $locale);

            $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);

            $attributeCorrelationRaw = $request->attributes->get('correlation_id');
            $attributeCorrelationId = is_string($attributeCorrelationRaw) ? $attributeCorrelationRaw : null;

            if ($attributeCorrelationId !== null && $attributeCorrelationId !== '') {
                $correlationId = $attributeCorrelationId;
            } elseif (app()->bound('request_correlation_id')) {
                $resolvedCorrelation = app()->make('request_correlation_id');
                $correlationId = is_string($resolvedCorrelation) && $resolvedCorrelation !== ''
                    ? $resolvedCorrelation
                    : Str::uuid()->toString();
            } else {
                $correlationId = Str::uuid()->toString();
            }

            $correlationHeaderConfig = config('app.correlation_header', 'X-Correlation-ID');
            $correlationHeader = is_string($correlationHeaderConfig) && $correlationHeaderConfig !== ''
                ? $correlationHeaderConfig
                : 'X-Correlation-ID';

            Log::withContext([
                'correlation_id' => $correlationId,
                'locale' => $locale,
                'error_code' => $exception->errorCode(),
                'request_path' => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::warning('Domain exception rendered.', [
                'exception' => $exception::class,
                'status' => $exception->status(),
                'translation_key' => $exception->translationKey(),
                'context' => $exception->context(),
            ]);

            $payload = [
                'error' => [
                    'code' => $exception->errorCode(),
                    'message' => $message,
                    'locale' => $locale,
                ],
                'meta' => [
                    'correlation_id' => $correlationId,
                    'timestamp' => now()->toIso8601String(),
                ],
            ];

            if ($exception->context() !== []) {
                $payload['error']['context'] = $exception->context();
            }

            return response()
                ->json($payload, $exception->status())
                ->header($correlationHeader, $correlationId)
                ->header('Content-Language', $locale);
        });
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\ApiServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\LocaleServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
        SecurityServiceProvider::class,
    ])
    ->create();
