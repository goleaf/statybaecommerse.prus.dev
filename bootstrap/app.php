<?php

declare(strict_types=1);

use App\Exceptions\Domain\DomainException;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Middleware\AddSecurityHeaders;
use App\Providers\SecurityServiceProvider;
use App\Services\TranslationService;
use App\Support\ApiErrorResponse;
use App\Support\ErrorCode;
use App\Support\ErrorCodes;
use App\Support\RequestContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

// Load the Filament compatibility shims before the application boots so the
// legacy class aliases are always available during early package discovery.
require_once __DIR__ . '/filament_compat.php';

$appEnvironment = (string) env('APP_ENV', 'production');
$queueConnection = (string) env('QUEUE_CONNECTION', 'sync');

if ($appEnvironment !== 'local' || $queueConnection !== 'sync') {
    $providers[] = App\Providers\HorizonServiceProvider::class;
}

$providers[] = App\Providers\LocaleServiceProvider::class;
if (! env('SKIP_FILAMENT_BOOT')) {
    $providers[] = App\Providers\Filament\AdminPanelProvider::class;
}
$providers[] = SecurityServiceProvider::class;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/frontend.php'));
            Route::middleware('web')
                ->group(base_path('routes/system-settings.php'));
            // Load admin routes
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            // Load reports routes
            Route::middleware('web')
                ->group(base_path('routes/reports.php'));
            Route::middleware('api')
                ->group(base_path('routes/monitoring.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(function (): void {
                    Route::get('/categories/tree', [CategoryController::class, 'tree'])
                        ->name('api.categories.tree');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(App\Http\Middleware\AddSecurityHeaders::class);
        $middleware->append(App\Http\Middleware\SetLocale::class);
        $middleware->append(App\Http\Middleware\SetFilamentLocale::class);
        // Handle user impersonation for admin support
        $middleware->append(App\Http\Middleware\HandleImpersonation::class);
        $middleware->append(AddSecurityHeaders::class);
        $middleware->throttleApi('api.default');
        // Register Spatie permission middlewares (Laravel 11+/12 style)
        $middleware->alias([
            'role'                   => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'             => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'permissions'            => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'     => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize'               => App\Http\Middleware\SetLocale::class,
            'partner.api'            => App\Http\Middleware\PartnerApiAuthenticate::class,
            'partner.api.auth'       => App\Http\Middleware\EnsurePartnerApiKey::class,
            'partner.api.scope'      => App\Http\Middleware\EnsurePartnerApiScope::class,
            'partner.api.rate_limit' => App\Http\Middleware\EnsurePartnerApiRateLimit::class,
            // Surface Sanctum's middleware aliases for SPA and token authentication.
            'sanctum.stateful' => Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'abilities'        => Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'ability'          => Laravel\Sanctum\Http\Middleware\CheckForAllAbilities::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldRenderJson = static function (Request $request): bool {
            if (RequestContext::isApiRequest($request)) {
                return true;
            }

            return $request->expectsJson() || $request->wantsJson();
        };

        $resolveLocale = static fn (Request $request): string => RequestContext::resolveLocale($request);
        $resolveTraceId = static fn (Request $request): string => RequestContext::resolveTraceId($request);
        $resolveCorrelationHeader = static fn (): string => RequestContext::correlationHeader();

        $exceptions->render(function (DomainException $exception, Request $request) {
            if (! RequestContext::isApiRequest($request) && ! $request->expectsJson() && ! $request->wantsJson()) {
                return null;
            }

            $locale = RequestContext::resolveLocale($request);
            $detail = TranslationService::get($exception->translationKey(), $exception->context(), $locale);
            $errorCode = $exception->errorCode()->value;

            Log::warning('Domain exception rendered.', [
                'exception'       => $exception::class,
                'status'          => $exception->status(),
                'translation_key' => $exception->translationKey(),
                'context'         => $exception->context(),
                'locale'          => $locale,
                'trace_id'        => RequestContext::resolveTraceId($request),
            ]);

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: $errorCode,
                detail: $detail,
                status: $exception->status(),
                title: ApiErrorResponse::titleFor($errorCode, $locale),
                context: $exception->context(),
                locale: $locale,
            );
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! RequestContext::isApiRequest($request)) {
                return null;
            }

            $locale = RequestContext::resolveLocale($request);
            $traceId = RequestContext::resolveTraceId($request);

            Log::withContext([
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::notice('Validation exception rendered.', [
                'errors' => $exception->errors(),
            ]);

            $fallbackMessages = [];
            $fallbackLocale = TranslationService::getFallbackLocale();

            $validator = $exception->validator;

            if ($validator instanceof ValidatorContract) {
                $originalLocale = app()->getLocale();
                $translator = null;
                $originalTranslatorLocale = null;
                $originalTranslatorFallback = null;

                if (app()->bound('translator')) {
                    /** @var object $translatorInstance */
                    $translatorInstance = app('translator');

                    $translator = $translatorInstance;

                    if (method_exists($translatorInstance, 'getLocale')) {
                        $originalTranslatorLocale = $translatorInstance->getLocale();
                    }

                    if (method_exists($translatorInstance, 'getFallback')) {
                        $originalTranslatorFallback = $translatorInstance->getFallback();
                    }
                }

                try {
                    // Re-run the validator with the fallback locale so we can surface
                    // a predictable English summary alongside the localized messages.
                    app()->setLocale($fallbackLocale);

                    // Ensure the translator aligns with the fallback locale so that
                    // rule replacements (like :attribute) resolve in English too.
                    if ($translator !== null) {
                        if (method_exists($translator, 'setLocale')) {
                            $translator->setLocale($fallbackLocale);
                        }

                        if (method_exists($translator, 'setFallback')) {
                            $translator->setFallback($fallbackLocale);
                        }
                    }

                    $fallbackValidator = app('validator')->make(
                        $validator->getData(),
                        $validator->getRules(),
                        []
                    );

                    if (property_exists($validator, 'customMessages')) {
                        $fallbackValidator->setCustomMessages($validator->customMessages);
                    }

                    if (property_exists($validator, 'fallbackMessages')) {
                        $fallbackValidator->setFallbackMessages($validator->fallbackMessages);
                    }

                    if (property_exists($validator, 'customAttributes')) {
                        $fallbackValidator->setAttributeNames($validator->customAttributes);
                    }

                    if (property_exists($validator, 'customValues')) {
                        $fallbackValidator->setValueNames($validator->customValues);
                    }

                    if (method_exists($validator, 'getPresenceVerifier') && method_exists($fallbackValidator, 'setPresenceVerifier')) {
                        $presenceVerifier = $validator->getPresenceVerifier();

                        if ($presenceVerifier !== null) {
                            $fallbackValidator->setPresenceVerifier($presenceVerifier);
                        }
                    }

                    $fallbackValidator->fails();

                    $fallbackMessages = $fallbackValidator->errors()->toArray();
                } finally {
                    // Always restore the previous locale so downstream formatters keep the
                    // request-scoped language selected by RequestContext.
                    app()->setLocale($originalLocale);

                    if ($translator !== null) {
                        if ($originalTranslatorLocale !== null && method_exists($translator, 'setLocale')) {
                            $translator->setLocale($originalTranslatorLocale);
                        }

                        if ($originalTranslatorFallback !== null && method_exists($translator, 'setFallback')) {
                            $translator->setFallback($originalTranslatorFallback);
                        }
                    }
                }
            }

            $violations = collect($exception->errors())
                ->map(static function (array $messages, string $field) use ($fallbackMessages): array {
                    $localizedMessages = array_values($messages);
                    $fallbackReason = $fallbackMessages[$field][0] ?? ($localizedMessages[0] ?? 'Invalid value.');

                    return [
                        'field'    => $field,
                        'messages' => $localizedMessages,
                        'reason'   => $fallbackReason,
                    ];
                })
                ->values()
                ->all();

            $detail = $exception->getMessage();
            if ($detail === '') {
                // Prefer localized defaults when Laravel does not provide a custom validation message.
                $detail = ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale)
                    ?? 'The given data was invalid.';
            }

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::VALIDATION_FAILED,
                detail: $detail,
                status: $exception->status,
                title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
                context: ['violations' => $violations],
                locale: $locale,
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! RequestContext::isApiRequest($request)) {
                if ($request->expectsJson()) {
                    // Preserve JSON semantics for Sanctum guarded routes that hit the admin panel without a browser session.
                    return response()->json(['message' => $exception->getMessage()], 401);
                }

                if (Route::has('filament.admin.auth.login')) {
                    // Mirror Filament's default behaviour by redirecting guests to the admin login screen.
                    return redirect()->guest(route('filament.admin.auth.login'));
                }

                // Fallback to the default login route so other guards keep working in tests.
                return redirect()->guest(Route::has('login') ? route('login') : '/login');
            }

            $locale = RequestContext::resolveLocale($request);
            $traceId = RequestContext::resolveTraceId($request);

            Log::withContext([
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::notice('Authentication exception rendered.', [
                'guards' => $exception->guards(),
            ]);

            $context = [];
            if ($exception->guards() !== []) {
                $context['guards'] = $exception->guards();
            }

            $detail = $exception->getMessage();
            if ($detail === '') {
                // Provide a localized baseline message when the exception lacks details.
                $detail = ErrorCodes::message(ErrorCodes::UNAUTHORIZED, $locale)
                    ?? 'Unauthenticated.';
            }

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::UNAUTHORIZED,
                detail: $detail,
                status: 401,
                title: ApiErrorResponse::titleFor(ErrorCodes::UNAUTHORIZED, $locale),
                context: $context,
                locale: $locale,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! RequestContext::isApiRequest($request)) {
                return null;
            }

            $locale = RequestContext::resolveLocale($request);
            $traceId = RequestContext::resolveTraceId($request);

            Log::withContext([
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::notice('Authorization exception rendered.', [
                'message' => $exception->getMessage(),
            ]);

            $context = [];
            if ($exception->getMessage() !== '') {
                $context['reason'] = $exception->getMessage();
            }

            $detail = $exception->getMessage();
            if ($detail === '') {
                // Provide a localized fallback when the authorization exception omits a message.
                $detail = ErrorCodes::message(ErrorCodes::FORBIDDEN, $locale)
                    ?? 'This action is unauthorized.';
            }

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::FORBIDDEN,
                detail: $detail,
                status: 403,
                title: ApiErrorResponse::titleFor(ErrorCodes::FORBIDDEN, $locale),
                context: $context,
                locale: $locale,
            );
        });

        $exceptions->render(function (Throwable $throwable, Request $request) use ($shouldRenderJson, $resolveLocale, $resolveTraceId, $resolveCorrelationHeader) {
            if ($throwable instanceof DomainException || ! $shouldRenderJson($request)) {
                return null;
            }

            $locale = $resolveLocale($request);
            $traceId = $resolveTraceId($request);
            $correlationHeader = $resolveCorrelationHeader();

            $errorCode = match (true) {
                $throwable instanceof ValidationException     => ErrorCode::ValidationFailed,
                $throwable instanceof AuthenticationException => ErrorCode::Unauthorized,
                $throwable instanceof AuthorizationException  => ErrorCode::Forbidden,
                $throwable instanceof ModelNotFoundException, $throwable instanceof NotFoundHttpException => ErrorCode::NotFound,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 404 => ErrorCode::NotFound,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 401 => ErrorCode::Unauthorized,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 403 => ErrorCode::Forbidden,
                $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() === 422 => ErrorCode::ValidationFailed,
                default                                                                             => ErrorCode::ServerError,
            };

            $status = match (true) {
                $throwable instanceof ValidationException    => $throwable->status,
                $throwable instanceof HttpExceptionInterface => $throwable->getStatusCode(),
                default                                      => $errorCode->defaultStatus(),
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

            $context = [
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ];

            Log::withContext($context);

            if ($throwable instanceof HttpResponseException) {
                return $throwable->getResponse();
            }

            if (RequestContext::isApiRequest($request)) {
                // Normalize PHP type errors (e.g. bad route param types) to a client error.
                if ($throwable instanceof TypeError) {
                    $reason = $throwable->getMessage();
                    if (is_string($reason)) {
                        $reason = preg_replace('/ in \/.*$/', '', $reason) ?? $reason;
                    }

                    Log::notice('Type error rendered as bad request.', [
                        'reason' => $reason,
                    ]);

                    $detail = ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale)
                        ?? 'Invalid request parameters.';

                    return ApiErrorResponse::problem(
                        request: $request,
                        errorCode: ErrorCodes::VALIDATION_FAILED,
                        detail: $detail,
                        status: 400,
                        title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
                        context: ['reason' => $reason],
                        locale: $locale,
                    );
                }

                if ($throwable instanceof HttpExceptionInterface) {
                    $status = $throwable->getStatusCode();
                    // Normalise common HTTP status codes to shared API error codes so
                    // integrators can implement consistent handling logic.
                    $code = match ($status) {
                        401     => ErrorCodes::UNAUTHORIZED,
                        403     => ErrorCodes::FORBIDDEN,
                        404     => ErrorCodes::NOT_FOUND,
                        429     => ErrorCodes::RATE_LIMITED,
                        default => ErrorCodes::SERVER_ERROR,
                    };

                    $rawHeaders = $throwable->getHeaders();
                    $sanitizedHeaders = [];

                    foreach ($rawHeaders as $headerName => $headerValue) {
                        if (! is_string($headerName)) {
                            continue;
                        }

                        $normalizedName = trim($headerName);

                        if ($normalizedName === '' || str_contains($normalizedName, "\r") || str_contains($normalizedName, "\n")) {
                            continue;
                        }

                        $values = is_array($headerValue) ? $headerValue : [$headerValue];
                        $normalizedValues = [];

                        foreach ($values as $value) {
                            if (! is_scalar($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
                                continue;
                            }

                            $stringValue = (string) $value;
                            $stringValue = preg_replace('/[\x00-\x1F\x7F]+/', '', $stringValue) ?? '';

                            if ($stringValue === '') {
                                continue;
                            }

                            $normalizedValues[] = $stringValue;
                        }

                        if ($normalizedValues === []) {
                            continue;
                        }

                        $sanitizedHeaders[$normalizedName] = count($normalizedValues) === 1
                            ? $normalizedValues[0]
                            : $normalizedValues;
                    }

                    Log::notice('HTTP exception rendered.', [
                        'exception' => $throwable::class,
                        'status'    => $status,
                        'headers'   => $sanitizedHeaders,
                    ]);

                    $detail = $throwable->getMessage() !== ''
                        ? $throwable->getMessage()
                        : (ErrorCodes::message($code, $locale)
                            ?? (SymfonyResponse::$statusTexts[$status] ?? 'HTTP Error'));

                    $context = $throwable->getHeaders() !== []
                        ? ['headers' => $throwable->getHeaders()]
                        : [];

                    if ($throwable instanceof AccessDeniedHttpException && $throwable->getMessage() !== '') {
                        // Mirror the explicit reason we provide for AuthorizationException so
                        // downstream clients can keep a consistent schema even when Symfony
                        // wraps the exception in an access denied HTTP variant.
                        $context['reason'] = $throwable->getMessage();
                    }

                    $response = ApiErrorResponse::problem(
                        request: $request,
                        errorCode: $code,
                        detail: $detail,
                        status: $status,
                        title: ApiErrorResponse::titleFor($code, $locale),
                        context: $context,
                        locale: $locale,
                    );

                    foreach ($throwable->getHeaders() as $name => $value) {
                        // Symfony's header bag expects scalar values to be strings, so normalise integers from rate limiting
                        // exceptions to avoid type errors while preserving array headers untouched.
                        $response->headers->set($name, is_array($value) ? $value : (string) $value);
                    }

                    return $response;
                }

                Log::error('Unhandled exception rendered.', [
                    'exception' => $throwable::class,
                    'message'   => $throwable->getMessage(),
                ]);

                $message = ErrorCodes::message(ErrorCodes::SERVER_ERROR, $locale)
                    ?? __('Something went wrong. Please try again later.', [], $locale);

                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::SERVER_ERROR,
                    detail: $message,
                    status: 500,
                    title: ApiErrorResponse::titleFor(ErrorCodes::SERVER_ERROR, $locale),
                    locale: $locale,
                );
            }

            Log::error('Unhandled exception rendered.', [
                'exception' => $throwable::class,
                'message'   => $throwable->getMessage(),
            ]);

            return response()
                ->view('errors.unexpected', [
                    'traceId'       => $traceId,
                    'correlationId' => $traceId,
                ], 500)
                ->header($correlationHeader, $traceId)
                ->header('Content-Language', $locale);
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
        // Register the Livewire testing helpers so resource hooks and test assets load reliably.
        $providers[] = App\Providers\LivewireTestingServiceProvider::class;
        $providers[] = SecurityServiceProvider::class;

        return $providers;
    })())
    ->create();

if (! $app->bound('request')) {
    $app->instance('request', Request::createFromGlobals());
}

return $app;
