<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

final class LocaleService
{
    public function __construct()
    {
        // Config values are resolved dynamically to support testing
    }

    /**
     * Resolve locale from request and set application locale.
     */
    public function resolveAndSetLocale(Request $request): string
    {
        $locale = $this->resolveLocale($request);

        // Set application locale
        App::setLocale($locale);
        app()->instance('request_locale', $locale);

        return $locale;
    }

    /**
     * Resolve locale from request without setting it.
     */
    public function resolveLocale(Request $request): string
    {
        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        $supportedLocales = $this->getSupportedLocales();
        $defaultLocale = $this->resolveDefaultLocale();
        $fallbackLocale = $this->resolveFallbackLocale();

        $candidateLocales = array_values(array_filter([
            $routeLocale,
            $queryLocale,
            Session::get('locale'),
            Session::get('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));

        $locale = null;
        $hasInvalidCandidates = false;

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            } else {
                $hasInvalidCandidates = true;
            }
        }

        if ($locale === null || ! in_array($locale, $supportedLocales, true)) {
            // If we had invalid candidates, use fallback locale
            // If we had no candidates at all, use default locale
            if ($hasInvalidCandidates) {
                if (in_array($fallbackLocale, $supportedLocales, true)) {
                    $locale = $fallbackLocale;
                } elseif (in_array($defaultLocale, $supportedLocales, true)) {
                    $locale = $defaultLocale;
                } elseif ($supportedLocales !== []) {
                    $locale = $supportedLocales[0];
                } else {
                    $locale = $defaultLocale;
                }
            } else {
                // No candidates provided, use default locale
                if (in_array($defaultLocale, $supportedLocales, true)) {
                    $locale = $defaultLocale;
                } elseif (in_array($fallbackLocale, $supportedLocales, true)) {
                    $locale = $fallbackLocale;
                } elseif ($supportedLocales !== []) {
                    $locale = $supportedLocales[0];
                } else {
                    $locale = $defaultLocale;
                }
            }
        }

        return $locale;
    }

    /**
     * Persist locale to session and cookie only if it has changed.
     */
    public function persistLocale(string $locale, Request $request): bool
    {
        $currentSessionLocale = Session::get('locale');
        $currentCookieLocale = $request->cookie('app_locale');

        // Skip persistence if locale hasn't changed
        if ($currentSessionLocale === $locale && $currentCookieLocale === $locale) {
            return false;
        }

        // Store in session and cookie for persistence
        Session::put('locale', $locale);
        Session::put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

        return true;
    }

    /**
     * Get the current application locale.
     */
    public function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Get supported locales.
     */
    public function getSupportedLocales(): array
    {
        return $this->resolveSupportedLocales();
    }

    /**
     * Check if a locale is supported.
     */
    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->getSupportedLocales(), true);
    }

    /**
     * Apply locale-specific configuration (currency mapping, etc.).
     */
    public function applyLocaleConfiguration(string $locale): void
    {
        $mappingConfig = config('app.locale_mapping', []);
        $mapping = is_array($mappingConfig) ? $mappingConfig : [];
        $localeMapping = $mapping[$locale] ?? null;

        if (is_array($localeMapping)) {
            $currency = $localeMapping['currency'] ?? null;
            if (is_string($currency) && $currency !== '') {
                Session::put('forced_currency', $currency);
            }
        }
    }

    private function resolveSupportedLocales(): array
    {
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

        return array_values(array_map(
            static fn (string $locale): string => trim($locale),
            $configuredLocales
        ));
    }

    private function resolveDefaultLocale(): string
    {
        $defaultLocaleConfig = config('app.locale', 'lt');

        return is_string($defaultLocaleConfig) && $defaultLocaleConfig !== ''
            ? $defaultLocaleConfig
            : 'lt';
    }

    private function resolveFallbackLocale(): string
    {
        $fallbackLocaleConfig = config('app.fallback_locale');

        return is_string($fallbackLocaleConfig) && $fallbackLocaleConfig !== ''
            ? $fallbackLocaleConfig
            : $this->resolveDefaultLocale();
    }
}
