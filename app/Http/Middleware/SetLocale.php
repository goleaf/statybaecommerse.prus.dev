<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Validate locale against configured supported locales
        $supported = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supported)
            ? $supported
            : array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedLocales = array_map('trim', $supportedLocales);

        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');
        // Try to honor Accept-Language header when it matches a supported locale
        $headerLocale = $request->getPreferredLanguage($supportedLocales);

        // Get locale from query, session (both keys), cookie, user preference, or Accept-Language
        $locale = $routeLocale
            ?? $queryLocale
            ?? Session::get('locale')
            ?? Session::get('app.locale')
            ?? $request->cookie('app_locale')
            ?? (auth()->check() ? auth()->user()->preferred_locale ?? null : null)
            ?? $headerLocale
            ?? config('app.locale', 'lt');

        if (! in_array($locale, $supportedLocales, true)) {
            $fallbackLocaleConfig = config('app.fallback_locale');
            $fallbackLocale = is_string($fallbackLocaleConfig) && $fallbackLocaleConfig !== ''
                ? $fallbackLocaleConfig
                : $defaultLocale;

            if (in_array($fallbackLocale, $supportedLocales, true)) {
                $locale = $fallbackLocale;
            } elseif (in_array($defaultLocale, $supportedLocales, true)) {
                $locale = $defaultLocale;
            } elseif ($supportedLocales !== []) {
                $locale = $supportedLocales[0];
            } else {
                $locale = $defaultLocale;
            }
        }

        // Set application locale
        App::setLocale($locale);
        app()->instance('request_locale', $locale);

        // Store in session and cookie for persistence
        Session::put('locale', $locale);
        Session::put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

        // Optionally map locale to currency/zone
        $mappingConfig = config('app.locale_mapping', []);
        $mapping = is_array($mappingConfig) ? $mappingConfig : [];
        $localeMapping = $mapping[$locale] ?? null;

        if (is_array($localeMapping)) {
            $currency = $localeMapping['currency'] ?? null;
            if (is_string($currency) && $currency !== '') {
                Session::put('forced_currency', $currency);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        if (! $response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }
}
