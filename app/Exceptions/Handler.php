<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
}
