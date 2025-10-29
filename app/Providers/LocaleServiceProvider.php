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
        // Set default locale to Lithuanian (from config)
        $defaultLocale = config('app.locale', 'lt');
        App::setLocale($defaultLocale);

        if (PHP_SAPI === 'cli' || app()->runningInConsole()) {
            return;
        }

        try {
            if (Session::has('locale')) {
                $locale = Session::get('locale');
                $supportedLocales = config('app.supported_locales', ['lt', 'en']);

                // Normalize supported locales array
                if (is_string($supportedLocales)) {
                    $supportedLocales = array_map('trim', explode(',', $supportedLocales));
                }

                if (is_array($supportedLocales) && in_array($locale, $supportedLocales, true)) {
                    App::setLocale($locale);
                }
            }
        } catch (Throwable $exception) {
            // When running in environments without an active session driver, gracefully ignore session locale overrides.
        }
    }
}
