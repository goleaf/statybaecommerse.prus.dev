<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

final class LocaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Locale resolution is now handled centrally by SetLocale middleware
        // Only set default locale for CLI/console commands that don't go through middleware
        if (PHP_SAPI === 'cli' || app()->runningInConsole()) {
            $defaultLocale = config('app.locale', 'lt');
            App::setLocale($defaultLocale);
        }
    }
}
