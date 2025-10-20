<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Throwable;

final class LocaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Set default locale to Lithuanian
        App::setLocale('lt');

        if (PHP_SAPI === 'cli' || app()->runningInConsole()) {
            return;
        }

        try {
            if (Session::has('locale')) {
                $locale = Session::get('locale');
                if (in_array($locale, ['lt', 'en'])) {
                    App::setLocale($locale);
                }
            }
        } catch (Throwable $exception) {
            // When running in environments without an active session driver, gracefully ignore session locale overrides.
        }
    }
}
