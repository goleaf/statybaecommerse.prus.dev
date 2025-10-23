<?php

use App\Exceptions\CodeStyleException;
use App\Support\ErrorCodes;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

// Load the Filament compatibility shims before the application boots so the
// legacy class aliases are always available during early package discovery.
require_once __DIR__ . '/filament_compat.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
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
            'partner.api' => App\Http\Middleware\PartnerApiAuthenticate::class,
            'partner.api.scope' => App\Http\Middleware\EnsurePartnerApiScope::class,
            'partner.api.rate_limit' => App\Http\Middleware\PartnerApiRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $resolveCorrelationId = static function (Request $request): string {
            $correlationId = $request->attributes->get('correlation_id');

            if (! is_string($correlationId) || $correlationId === '') {
                $correlationId = (string) Str::uuid();
                $request->attributes->set('correlation_id', $correlationId);
            }

            return $correlationId;
        };

        $exceptions->report(function (\Throwable $throwable) use ($resolveCorrelationId): void {
            $request = request();
            $correlationId = $request instanceof Request
                ? $resolveCorrelationId($request)
                : (string) Str::uuid();

            Log::error($throwable->getMessage(), [
                'exception' => $throwable,
                'correlation_id' => $correlationId,
            ]);
        });

        $exceptions->render(function (\Throwable $throwable, Request $request) use ($resolveCorrelationId) {
            if (! $request->expectsJson() && ! $request->wantsJson()) {
                return null;
            }

            $correlationId = $resolveCorrelationId($request);

            [$status, $code] = match (true) {
                $throwable instanceof ValidationException => [422, ErrorCodes::VALIDATION_FAILED],
                $throwable instanceof AuthenticationException => [401, ErrorCodes::AUTHENTICATION_FAILED],
                $throwable instanceof AuthorizationException => [403, ErrorCodes::AUTHORIZATION_FAILED],
                $throwable instanceof ModelNotFoundException => [404, ErrorCodes::MODEL_NOT_FOUND],
                $throwable instanceof NotFoundHttpException => [404, ErrorCodes::ROUTE_NOT_FOUND],
                $throwable instanceof MethodNotAllowedHttpException => [405, ErrorCodes::METHOD_NOT_ALLOWED],
                $throwable instanceof TooManyRequestsHttpException => [429, ErrorCodes::TOO_MANY_REQUESTS],
                $throwable instanceof CodeStyleException => [422, ErrorCodes::CODE_STYLE_VIOLATION],
                $throwable instanceof HttpExceptionInterface => [
                    $throwable->getStatusCode(),
                    match ($throwable->getStatusCode()) {
                        503 => ErrorCodes::SERVICE_UNAVAILABLE,
                        404 => ErrorCodes::ROUTE_NOT_FOUND,
                        405 => ErrorCodes::METHOD_NOT_ALLOWED,
                        401 => ErrorCodes::AUTHENTICATION_FAILED,
                        403 => ErrorCodes::AUTHORIZATION_FAILED,
                        429 => ErrorCodes::TOO_MANY_REQUESTS,
                        default => ErrorCodes::UNKNOWN_ERROR,
                    },
                ],
                $throwable instanceof \RuntimeException => [500, ErrorCodes::RUNTIME_ERROR],
                default => [500, ErrorCodes::UNKNOWN_ERROR],
            };

            $messageKey = "errors.$code";
            $message = trans($messageKey);

            if ($message === $messageKey) {
                $fallbackKey = 'errors.' . ErrorCodes::UNKNOWN_ERROR;
                $fallback = trans($fallbackKey);
                $message = $fallback === $fallbackKey
                    ? __('An unexpected error occurred.')
                    : $fallback;
            }

            return response()->json([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'correlation_id' => $correlationId,
                ],
            ], $status);
        });
    })
    ->withProviders((function (): array {
        $providers = [
            App\Providers\AuthServiceProvider::class,
            App\Providers\ApiServiceProvider::class,
            App\Providers\ModelScopeServiceProvider::class,
        ];

        $appEnvironment = (string) env('APP_ENV', 'production');
        $queueConnection = (string) env('QUEUE_CONNECTION', 'sync');

        if ($appEnvironment !== 'local' || $queueConnection !== 'sync') {
            $providers[] = App\Providers\HorizonServiceProvider::class;
        }

        $providers[] = App\Providers\LocaleServiceProvider::class;
        $providers[] = App\Providers\Filament\AdminPanelProvider::class;
        $providers[] = SecurityServiceProvider::class;

        return $providers;
    })())
    ->create();
