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
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve the list of supported locales declared in configuration.
        $supportedConfig = config('app.supported_locales', ['lt', 'en']);
        $configuredLocales = [];

        if (is_array($supportedConfig)) {
            $configuredLocales = array_filter($supportedConfig, static fn ($locale): bool => is_string($locale) && $locale !== '');
        } elseif (is_string($supportedConfig)) {
            $configuredLocales = array_filter(
                array_map(
                    static fn (string $locale): string => trim($locale),
                    explode(',', (string) $supportedConfig)
                ),
                static fn (string $locale): bool => $locale !== ''
            );
        }

        $supportedLocales = array_values(array_map(
            static fn (string $locale): string => trim($locale),
            $configuredLocales
        ));

        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        // Honor the Accept-Language header if it matches a supported locale.
        $headerLocale = null;
        if ($request->hasHeader('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($supportedLocales);
            if (is_string($preferred) && $preferred !== '') {
                $headerLocale = $preferred;
            } else {
                // Manually inspect the header to gracefully handle regional variants (e.g., en-GB).
                foreach ($request->getLanguages() as $language) {
                    $primary = strtolower(str_replace('_', '-', $language));
                    $segment = explode('-', (string) $primary)[0] ?? '';

                    if ($segment !== '' && in_array($segment, $supportedLocales, true)) {
                        $headerLocale = $segment;
                        break;
                    }
                }
            }
        }

        // Get locale from query, header, session, cookie, or user preference
        $defaultLocaleConfig = config('app.locale', 'lt');
        $defaultLocale = is_string($defaultLocaleConfig) && $defaultLocaleConfig !== ''
            ? $defaultLocaleConfig
            : 'lt';

        $candidateLocales = array_values(array_filter([
            $routeLocale,
            $queryLocale,
            $headerLocale,
            Session::get('locale'),
            Session::get('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));

        $locale = $defaultLocale;

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }

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
            // Ensure downstream responses advertise the language we resolved for this request.
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }
}
