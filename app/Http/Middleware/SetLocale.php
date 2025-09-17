<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

final class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        // Get locale from query, session (both keys), cookie, or user preference
        $locale = $routeLocale
            ?? $queryLocale
            ?? Session::get('locale')
            ?? Session::get('app.locale')
            ?? $request->cookie('app_locale')
            ?? (auth()->check() ? auth()->user()->preferred_locale ?? null : null)
            ?? config('app.locale', 'lt');

        // Validate locale against configured supported locales
        $supported = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supported)
            ? $supported
            : array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedLocales = array_map('trim', $supportedLocales);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = (string) (config('app.locale', 'lt'));
        }

        // Set application locale
        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        // Store in session and cookie for persistence
        Session::put('locale', $locale);
        Session::put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

        // Optionally map locale to currency/zone
        $mapping = (array) config('app.locale_mapping', []);
        if (isset($mapping[$locale]['currency']) && is_string($mapping[$locale]['currency'])) {
            Session::put('forced_currency', $mapping[$locale]['currency']);
        }

        return $next($request);
    }
}
