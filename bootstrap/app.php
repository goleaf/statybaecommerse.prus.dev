<?php

use App\Http\Middleware\AttachCorrelationId;
use App\Http\Middleware\AddSecurityHeaders;
use App\Providers\SecurityServiceProvider;
use App\Services\TranslationService;
use App\Support\ErrorCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $middleware->append(AddSecurityHeaders::class);
        // Register Spatie permission middlewares (Laravel 11+/12 style)
        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'permissions' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize' => App\Http\Middleware\SetLocale::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldRenderJson = static function (Request $request): bool {
            return $request->expectsJson() || $request->is('api/*');
        };

        $resolveLocale = static function (Request $request): string {
            $availableLocales = TranslationService::getAvailableLocales();
            $locale = TranslationService::getDefaultLocale();
            $header = $request->headers->get('Accept-Language');

            if (is_string($header) && $header !== '') {
                $candidates = array_map('trim', explode(',', $header));

                foreach ($candidates as $candidate) {
                    if ($candidate === '') {
                        continue;
                    }

                    $parts = array_map('trim', explode(';', $candidate));
                    $language = strtolower($parts[0]);
                    $quality = 1.0;

                    foreach (array_slice($parts, 1) as $parameter) {
                        if (str_starts_with($parameter, 'q=')) {
                            $value = substr($parameter, 2);
                            if (is_numeric($value)) {
                                $quality = (float) $value;
                            }

                            break;
                        }
                    }

                    if ($quality < 0.7) {
                        continue;
                    }

                    if (in_array($language, $availableLocales, true)) {
                        $locale = $language;
                        break;
                    }
                }
            }

            if (! in_array($locale, $availableLocales, true)) {
                $locale = TranslationService::getDefaultLocale();
            }

            app()->setLocale($locale);
            app()->instance('request_locale', $locale);

            return $locale;
        };

        $resolveTraceId = static function (Request $request): string {
            $attributeCorrelationRaw = $request->attributes->get('correlation_id');
            $attributeCorrelationId = is_string($attributeCorrelationRaw) ? $attributeCorrelationRaw : null;

            if ($attributeCorrelationId !== null && $attributeCorrelationId !== '') {
                return $attributeCorrelationId;
            }

            if (app()->bound('request_correlation_id')) {
                $resolvedCorrelation = app()->make('request_correlation_id');

                if (is_string($resolvedCorrelation) && $resolvedCorrelation !== '') {
                    return $resolvedCorrelation;
                }
            }

            return Str::uuid()->toString();
        };

        $resolveCorrelationHeader = static function (): string {
            $correlationHeaderConfig = config('app.correlation_header', 'X-Correlation-ID');

            return is_string($correlationHeaderConfig) && $correlationHeaderConfig !== ''
                ? $correlationHeaderConfig
                : 'X-Correlation-ID';
        };

        $exceptions->render(function (DomainException $exception, Request $request) use ($shouldRenderJson, $resolveLocale, $resolveTraceId, $resolveCorrelationHeader) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            $locale = $resolveLocale($request);
            $traceId = $resolveTraceId($request);
            $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);
            $correlationHeader = $resolveCorrelationHeader();

            Log::withContext([
                'trace_id' => $traceId,
                'locale' => $locale,
                'error_code' => $exception->errorCode()->value,
                'request_path' => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::warning('Domain exception rendered.', [
                'exception' => $exception::class,
                'status' => $exception->status(),
                'translation_key' => $exception->translationKey(),
                'context' => $exception->context(),
            ]);

            $details = [
                'locale' => $locale,
            ];

            if ($exception->context() !== []) {
                $details['context'] = $exception->context();
            }

            return response()
                ->json([
                    'code' => $exception->errorCode()->value,
                    'message' => $message,
                    'details' => $details,
                    'trace_id' => $traceId,
                ], $exception->status())
                ->header($correlationHeader, $traceId)
                ->header('Content-Language', $locale);
        });

        $exceptions->render(function (Throwable $throwable, Request $request) use ($shouldRenderJson, $resolveLocale, $resolveTraceId, $resolveCorrelationHeader) {
            if ($throwable instanceof DomainException || ! $shouldRenderJson($request)) {
                return null;
            }

            $locale = $resolveLocale($request);
            $traceId = $resolveTraceId($request);
            $correlationHeader = $resolveCorrelationHeader();

            $errorCode = match (true) {
                $throwable instanceof ValidationException => ErrorCode::ValidationFailed,
                $throwable instanceof AuthenticationException => ErrorCode::Unauthorized,
                $throwable instanceof AuthorizationException => ErrorCode::Forbidden,
                $throwable instanceof ModelNotFoundException, $throwable instanceof NotFoundHttpException => ErrorCode::NotFound,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 404 => ErrorCode::NotFound,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 401 => ErrorCode::Unauthorized,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 403 => ErrorCode::Forbidden,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 422 => ErrorCode::ValidationFailed,
                default => ErrorCode::ServerError,
            };

            $status = match (true) {
                $throwable instanceof ValidationException => $throwable->status,
                $throwable instanceof HttpExceptionInterface => $throwable->getStatusCode(),
                default => $errorCode->defaultStatus(),
            };

            $details = [
                'locale' => $locale,
            ];

            if ($throwable instanceof ValidationException) {
                $details['errors'] = $throwable->errors();
            }

            if ($throwable instanceof HttpExceptionInterface && $throwable->getMessage() !== '') {
                $details['hint'] = $throwable->getMessage();
            }

            $message = TranslationService::get($errorCode->translationKey(), [], $locale);

            Log::withContext([
                'trace_id' => $traceId,
                'locale' => $locale,
                'error_code' => $errorCode->value,
                'request_path' => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::error('Exception rendered as structured API response.', [
                'exception' => $throwable::class,
                'status' => $status,
                'message' => $throwable->getMessage(),
            ]);

            return response()
                ->json([
                    'code' => $errorCode->value,
                    'message' => $message,
                    'details' => $details,
                    'trace_id' => $traceId,
                ], $status)
                ->header($correlationHeader, $traceId)
                ->header('Content-Language', $locale);
        });
    })
    ->withProviders(function (): array {
        $providers = [
            App\Providers\AuthServiceProvider::class,
            App\Providers\ApiServiceProvider::class,
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
    })
    ->create();
