<?php

declare(strict_types=1);

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\AttachCorrelationId;
use App\Providers\SecurityServiceProvider;
use App\Services\TranslationService;
use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use App\Support\RequestContext;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

require_once __DIR__ . '/../app/Support/filament_compat.php';

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

$app = Application::configure(basePath: dirname(__DIR__))
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
            Route::middleware('api')
                ->group(base_path('routes/monitoring.php'));
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
            'role'               => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'permissions'        => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize'           => App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (DomainException $exception, Request $request) {
            $locale = RequestContext::resolveLocale($request);
            $traceId = RequestContext::resolveTraceId($request);
            $correlationHeader = RequestContext::correlationHeader();

            $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);

            Log::withContext([
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'error_code'     => $exception->errorCode(),
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ]);

            Log::warning('Domain exception rendered.', [
                'exception'       => $exception::class,
                'status'          => $exception->status(),
                'translation_key' => $exception->translationKey(),
                'context'         => $exception->context(),
            ]);

            $payload = [
                'error' => [
                    'code'    => $exception->errorCode(),
                    'message' => $message,
                    'locale'  => $locale,
                ],
                'meta' => [
                    'trace_id'       => $traceId,
                    'correlation_id' => $traceId,
                    'timestamp'      => now()->toIso8601String(),
                ],
            ];

            if ($exception->context() !== []) {
                $payload['error']['context'] = $exception->context();
            }

            return response()
                ->json($payload, $exception->status())
                ->header($correlationHeader, $traceId)
                ->header('Content-Language', $locale);
        });

        $exceptions->render(function (Throwable $throwable, Request $request) {
            if ($throwable instanceof DomainException) {
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

            Log::error('Unhandled exception rendered.', [
                'exception' => $throwable::class,
                'message'   => $throwable->getMessage(),
            ]);

            $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: $exception->errorCode(),
                detail: $message,
                status: $exception->status(),
                title: ApiErrorResponse::titleFor($exception->errorCode()),
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

            $violations = collect($exception->errors())
                ->map(static fn (array $messages, string $field): array => [
                    'field'    => $field,
                    'messages' => array_values($messages),
                    'reason'   => $messages[0] ?? 'Invalid value.',
                ])
                ->values()
                ->all();

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::VALIDATION_FAILED,
                detail: $exception->getMessage() !== '' ? $exception->getMessage() : 'The given data was invalid.',
                status: $exception->status,
                title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED),
                context: ['violations' => $violations],
                locale: $locale,
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
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

            Log::notice('Authentication exception rendered.', [
                'guards' => $exception->guards(),
            ]);

            $context = [];
            if ($exception->guards() !== []) {
                $context['guards'] = $exception->guards();
            }

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::UNAUTHORIZED,
                detail: $exception->getMessage() !== '' ? $exception->getMessage() : 'Unauthenticated.',
                status: 401,
                title: ApiErrorResponse::titleFor(ErrorCodes::UNAUTHORIZED),
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

            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::FORBIDDEN,
                detail: $exception->getMessage() !== '' ? $exception->getMessage() : 'This action is unauthorized.',
                status: 403,
                title: ApiErrorResponse::titleFor(ErrorCodes::FORBIDDEN),
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

            $context = [
                'trace_id'       => $traceId,
                'correlation_id' => $traceId,
                'locale'         => $locale,
                'request_path'   => $request->path(),
                'request_method' => $request->method(),
            ];

            Log::withContext($context);

            if (RequestContext::isApiRequest($request)) {
                if ($throwable instanceof HttpExceptionInterface) {
                    $status = $throwable->getStatusCode();
                    $code = match ($status) {
                        401     => ErrorCodes::UNAUTHORIZED,
                        403     => ErrorCodes::FORBIDDEN,
                        404     => ErrorCodes::NOT_FOUND,
                        default => ErrorCodes::SERVER_ERROR,
                    };

                    Log::notice('HTTP exception rendered.', [
                        'exception' => $throwable::class,
                        'status'    => $status,
                        'headers'   => $throwable->getHeaders(),
                    ]);

                    $detail = $throwable->getMessage() !== ''
                        ? $throwable->getMessage()
                        : (SymfonyResponse::$statusTexts[$status] ?? 'HTTP Error');

                    $response = ApiErrorResponse::problem(
                        request: $request,
                        errorCode: $code,
                        detail: $detail,
                        status: $status,
                        title: ApiErrorResponse::titleFor($code),
                        context: $throwable->getHeaders() !== [] ? ['headers' => $throwable->getHeaders()] : [],
                        locale: $locale,
                    );

                    foreach ($throwable->getHeaders() as $name => $value) {
                        $response->headers->set($name, $value);
                    }

                    return $response;
                }

                Log::error('Unhandled exception rendered.', [
                    'exception' => $throwable::class,
                    'message'   => $throwable->getMessage(),
                ]);

                $message = Lang::get('errors.messages.server_error', [], $locale);
                if ($message === 'errors.messages.server_error') {
                    $message = __('Something went wrong. Please try again later.', [], $locale);
                }

                $payload = [
                    'error' => [
                        'code'    => ErrorCodes::SERVER_ERROR,
                        'message' => $message,
                        'locale'  => $locale,
                    ],
                    'meta' => [
                        'trace_id'       => $traceId,
                        'correlation_id' => $traceId,
                        'timestamp'      => now()->toIso8601String(),
                    ],
                ];

                return response()
                    ->json($payload, 500)
                    ->header($correlationHeader, $traceId)
                    ->header('Content-Language', $locale);
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
    ->withProviders($providers)
    ->create();

$app->instance('request', Request::capture());

$app->singleton('db.factory', static fn (Application $app) => new ConnectionFactory($app));
$app->singleton('db', static fn (Application $app) => new DatabaseManager($app, $app['db.factory']));

$app->booting(function (Application $app): void {
    Model::setConnectionResolver($app['db']);
    Model::setEventDispatcher($app['events']);
});

$app->make(ConsoleKernel::class)->bootstrap();

$app['config']->set('database.default', $app['config']->get('database.default', 'sqlite'));
$app['config']->set('database.connections.sqlite', array_replace([
    'driver'                  => 'sqlite',
    'database'                => env('DB_DATABASE', ':memory:'),
    'prefix'                  => '',
    'foreign_key_constraints' => true,
], $app['config']->get('database.connections.sqlite', [])));

return $app;
