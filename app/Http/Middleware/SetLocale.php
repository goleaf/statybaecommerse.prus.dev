<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TranslationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        // Determine supported locales from configuration
        $supported = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supported)
            ? $supported
            : array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedLocales = array_values(array_filter(array_map('trim', $supportedLocales)));

        $headerLocale = $request->getPreferredLanguage($supportedLocales);

        // Get locale from query, session (both keys), cookie, Accept-Language header, or user preference
        $locale = $routeLocale
            ?? $queryLocale
            ?? Session::get('locale')
            ?? Session::get('app.locale')
            ?? $request->cookie('app_locale')
            ?? (auth()->check() ? auth()->user()->preferred_locale ?? null : null)
            ?? (is_string($headerLocale) && $headerLocale !== '' ? $headerLocale : null)
            ?? config('app.locale', 'lt');

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = is_string($headerLocale) && in_array($headerLocale, $supportedLocales, true)
                ? $headerLocale
                : (string) (config('app.locale', 'lt'));
        }

        // Set application locale
        App::setLocale($locale);
        $request->attributes->set('resolved_locale', $locale);

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
