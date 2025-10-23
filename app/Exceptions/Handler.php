<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use App\Support\RequestContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Throwable;
use TypeError;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     * Keeping the guard in place prevents sensitive credentials from leaking into session flashes.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * The closure allows us to hook additional logging or reporting later without
     * changing Laravel's default behaviour while restoring the missing handler class.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Intentionally left blank for now so default exception reporting remains intact.
            // Future enhancements can log domain specific context here without touching bootstrap.
        });
    }

    /**
     * Report or log an exception.
     *
     * For API requests, avoid treating validation issues as application errors
     * and downgrade type errors arising from bad route parameters to warnings.
     */
    public function report(Throwable $e): void
    {
        if ($e instanceof ValidationException) {
            // Don't spam logs for user input errors.
            return;
        }

        if ($e instanceof TypeError) {
            // Surface a concise warning to aid debugging without full stack traces.
            Log::warning('Type error triggered by request parameters.', [
                'message' => $e->getMessage(),
            ]);

            return;
        }

        parent::report($e);
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            // APIs should continue receiving structured JSON payloads instead of redirects.
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        }

        if (Route::has('filament.admin.auth.login')) {
            // Filament routes in tests expect a redirect to the admin login screen instead of an exception.
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        $fallback = Route::has('login') ? route('login') : '/login';

        // Preserve Laravel's default redirect behaviour for any other web guard.
        return redirect()->guest($fallback);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * Keep API responses consistent with our RFC 7807 problem format.
     */
    public function render($request, Throwable $e)
    {
        // Only customize API responses; defer to the framework for web views.
        if ($request instanceof Request && RequestContext::isApiRequest($request)) {
            $locale = RequestContext::resolveLocale($request);

            if ($e instanceof ValidationException) {
                $violations = collect($e->errors())
                    ->map(static function (array $messages, string $field): array {
                        $localizedMessages = array_values($messages);

                        return [
                            'field'    => $field,
                            'messages' => $localizedMessages,
                            'reason'   => $localizedMessages[0] ?? 'Invalid value.',
                        ];
                    })
                    ->values()
                    ->all();

                $detail = $e->getMessage() !== ''
                    ? $e->getMessage()
                    : (ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale) ?? 'The given data was invalid.');

                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::VALIDATION_FAILED,
                    detail: $detail,
                    status: $e->status,
                    title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
                    context: ['violations' => $violations],
                    locale: $locale,
                );
            }

            if ($e instanceof TypeError) {
                // Treat parameter type mismatches as a bad request rather than a server error.
                $reason = $e->getMessage();

                // Trim noisy details to avoid leaking internals in responses.
                if (is_string($reason)) {
                    $reason = preg_replace('/ in \/.*$/', '', $reason) ?? $reason;
                }

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
        }

        return parent::render($request, $e);
    }
}
