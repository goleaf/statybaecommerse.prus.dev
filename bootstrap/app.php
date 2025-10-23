<?php

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\AttachCorrelationId;
use App\Services\TranslationService;
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
            'partner.api' => App\Http\Middleware\PartnerApiAuthenticate::class,
            'partner.api.scope' => App\Http\Middleware\EnsurePartnerApiScope::class,
            'partner.api.rate_limit' => App\Http\Middleware\PartnerApiRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $correlationId = static function (Request $request): string {
            $current = $request->attributes->get('correlation_id');
            if (is_string($current) && $current !== '') {
                return $current;
            }

            if (app()->bound('request_correlation_id')) {
                $bound = app()->make('request_correlation_id');
                if (is_string($bound) && $bound !== '') {
                    $request->attributes->set('correlation_id', $bound);

                    return $bound;
                }
            }

            $generated = Str::uuid()->toString();
            $request->attributes->set('correlation_id', $generated);
            app()->instance('request_correlation_id', $generated);

            return $generated;
        };

        $resolveLocale = static function (Request $request): string {
            $availableLocales = TranslationService::getAvailableLocales();
            $resolved = $request->attributes->get('resolved_locale');
            if (is_string($resolved) && $resolved !== '' && in_array($resolved, $availableLocales, true)) {
                return $resolved;
            }
            $current = app()->getLocale();
            if (is_string($current) && $current !== '' && in_array($current, $availableLocales, true)) {
                return $current;
            }

            $preferred = $request->getPreferredLanguage($availableLocales);
            if (is_string($preferred) && $preferred !== '') {
                return $preferred;
            }

            return TranslationService::getDefaultLocale();
        };

        $respond = static function (
            Request $request,
            string $errorCode,
            int $status,
            array $context = [],
            array $errorExtras = [],
            array $metaExtras = []
        ) use ($correlationId, $resolveLocale) {
            $locale = $resolveLocale($request);
            app()->setLocale($locale);

            $message = TranslationService::get(ErrorCodes::translationKey($errorCode), $context, $locale);

            $correlation = $correlationId($request);

            $payload = [
                'error' => array_merge([
                    'code' => $errorCode,
                    'message' => $message,
                    'status' => $status,
                ], $errorExtras),
                'meta' => array_merge([
                    'correlation_id' => $correlation,
                    'locale' => $locale,
                ], $metaExtras),
            ];

            $response = response()->json($payload, $status);
            $response->headers->set(config('app.correlation_header', 'X-Correlation-ID'), $correlation);

            return $response;
        };

        $logException = static function (
            Throwable $throwable,
            Request $request,
            string $errorCode,
            int $status,
            array $context = [],
            ?string $level = null
        ) use ($correlationId): void {
            $level ??= $status >= 500 ? 'error' : 'warning';

            Log::log($level, 'Handled request exception.', [
                'error_code' => $errorCode,
                'status' => $status,
                'correlation_id' => $correlationId($request),
                'context' => $context,
                'request' => [
                    'method' => $request->getMethod(),
                    'path' => $request->path(),
                ],
                'exception' => $throwable,
            ]);
        };

        $exceptions->render(function (DomainException $exception, Request $request) use ($respond, $logException) {
            $status = $exception->status();
            $errorCode = $exception->errorCode();
            $context = $exception->context();

            $logException($exception, $request, $errorCode, $status, $context, 'warning');

            return $respond($request, $errorCode, $status, $context);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($respond, $logException) {
            $status = 401;
            $errorCode = ErrorCodes::UNAUTHORIZED;

            $logException($exception, $request, $errorCode, $status, [], 'notice');

            return $respond($request, $errorCode, $status);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($respond, $logException) {
            $status = 403;
            $errorCode = ErrorCodes::FORBIDDEN;

            $logException($exception, $request, $errorCode, $status, [], 'notice');

            return $respond($request, $errorCode, $status);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($respond, $logException) {
            $status = $exception->status;
            $errorCode = ErrorCodes::VALIDATION_FAILED;
            $errors = $exception->errors();

            $logException($exception, $request, $errorCode, $status, ['errors' => $errors], 'info');

            return $respond(
                $request,
                $errorCode,
                $status,
                context: [],
                errorExtras: ['details' => $errors],
            );
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($respond, $logException) {
            $status = 404;
            $errorCode = ErrorCodes::NOT_FOUND;

            $logException(
                $exception,
                $request,
                $errorCode,
                $status,
                ['model' => $exception->getModel()],
                'notice'
            );

            return $respond($request, $errorCode, $status);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($respond, $logException) {
            $status = $exception->getStatusCode();
            $errorCode = match (true) {
                $exception instanceof NotFoundHttpException => ErrorCodes::NOT_FOUND,
                $exception instanceof MethodNotAllowedHttpException => ErrorCodes::METHOD_NOT_ALLOWED,
                $exception instanceof TooManyRequestsHttpException => ErrorCodes::TOO_MANY_REQUESTS,
                $status === 400 => ErrorCodes::BAD_REQUEST,
                $status === 401 => ErrorCodes::UNAUTHORIZED,
                $status === 403 => ErrorCodes::FORBIDDEN,
                $status >= 500 => ErrorCodes::SERVER_ERROR,
                default => ErrorCodes::BAD_REQUEST,
            };

            $level = $status >= 500 ? 'error' : 'warning';
            $logException($exception, $request, $errorCode, $status, [], $level);

            return $respond($request, $errorCode, $status);
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($respond, $logException) {
            $status = 500;
            $errorCode = ErrorCodes::SERVER_ERROR;

            $logException($exception, $request, $errorCode, $status);

            return $respond($request, $errorCode, $status);
        });
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\ApiServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\LocaleServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
    ])
    ->create();
