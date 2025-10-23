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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            return $request->attributes->get('correlation_id')
                ?? (app()->bound('request_correlation_id')
                    ? (string) app()->make('request_correlation_id')
                    : Str::uuid()->toString());
        };

        $resolveLocale = static function (Request $request): string {
            $availableLocales = TranslationService::getAvailableLocales();
            $resolved = $request->attributes->get('resolved_locale');
            $preferred = $request->getPreferredLanguage($availableLocales);
            $current = app()->getLocale();

            $locale = is_string($resolved) && $resolved !== ''
                ? $resolved
                : (is_string($preferred) && $preferred !== ''
                    ? $preferred
                    : (is_string($current) && $current !== ''
                        ? $current
                        : TranslationService::getDefaultLocale()));

            if (! in_array($locale, $availableLocales, true)) {
                $locale = TranslationService::getDefaultLocale();
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

            return $locale;
        };

        $buildResponse = static function (
            Request $request,
            string $code,
            string $translationKey,
            array $context,
            int $status,
            array $details = [],
        ) use ($resolveCorrelationId, $resolveLocale): array {
            $locale = $resolveLocale($request);
            $message = TranslationService::get($translationKey, $context, $locale);
            $correlationId = $resolveCorrelationId($request);

            $payload = [
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
                'correlation_id' => $correlationId,
            ];

            if ($details !== []) {
                $payload['error']['details'] = $details;
            }

            return [$correlationId, new JsonResponse($payload, $status)];
        };

        $exceptions->render(function (DomainException $exception, Request $request) use ($buildResponse) {
            [$correlationId, $response] = $buildResponse(
                $request,
                $exception->errorCode(),
                $exception->translationKey(),
                $exception->context(),
                $exception->status(),
            );

            Log::warning($exception->getMessage(), [
                'error_code' => $exception->errorCode(),
                'context' => $exception->context(),
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($buildResponse) {
            $details = ['fields' => $exception->errors()];

            [$correlationId, $response] = $buildResponse(
                $request,
                ErrorCodes::VALIDATION_FAILED,
                ErrorCodes::messageKey(ErrorCodes::VALIDATION_FAILED),
                [],
                $exception->status,
                $details,
            );

            Log::info('Validation failed', [
                'errors' => $exception->errors(),
                'correlation_id' => $correlationId,
            ]);

            return $response;
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($buildResponse) {
            [$correlationId, $response] = $buildResponse(
                $request,
                ErrorCodes::HTTP_UNAUTHORIZED,
                ErrorCodes::messageKey(ErrorCodes::HTTP_UNAUTHORIZED),
                [],
                401,
            );

            Log::warning($exception->getMessage(), [
                'error_code' => ErrorCodes::HTTP_UNAUTHORIZED,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($buildResponse) {
            [$correlationId, $response] = $buildResponse(
                $request,
                ErrorCodes::HTTP_FORBIDDEN,
                ErrorCodes::messageKey(ErrorCodes::HTTP_FORBIDDEN),
                [],
                403,
            );

            Log::warning($exception->getMessage(), [
                'error_code' => ErrorCodes::HTTP_FORBIDDEN,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($buildResponse) {
            [$correlationId, $response] = $buildResponse(
                $request,
                ErrorCodes::HTTP_NOT_FOUND,
                ErrorCodes::messageKey(ErrorCodes::HTTP_NOT_FOUND),
                [],
                404,
            );

            Log::warning($exception->getMessage(), [
                'error_code' => ErrorCodes::HTTP_NOT_FOUND,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($buildResponse) {
            $status = $exception->getStatusCode();

            $code = match ($status) {
                400 => ErrorCodes::HTTP_BAD_REQUEST,
                401 => ErrorCodes::HTTP_UNAUTHORIZED,
                403 => ErrorCodes::HTTP_FORBIDDEN,
                404 => ErrorCodes::HTTP_NOT_FOUND,
                405 => ErrorCodes::HTTP_METHOD_NOT_ALLOWED,
                429 => ErrorCodes::HTTP_TOO_MANY_REQUESTS,
                default => ErrorCodes::INTERNAL_SERVER_ERROR,
            };

            [$correlationId, $response] = $buildResponse(
                $request,
                $code,
                ErrorCodes::messageKey($code),
                [],
                $status,
            );

            Log::warning($exception->getMessage(), [
                'status' => $status,
                'error_code' => $code,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($buildResponse) {
            [$correlationId, $response] = $buildResponse(
                $request,
                ErrorCodes::INTERNAL_SERVER_ERROR,
                ErrorCodes::messageKey(ErrorCodes::INTERNAL_SERVER_ERROR),
                [],
                500,
            );

            Log::error($exception->getMessage(), [
                'error_code' => ErrorCodes::INTERNAL_SERVER_ERROR,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return $response;
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
