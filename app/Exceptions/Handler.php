<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Throwable;

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
}
