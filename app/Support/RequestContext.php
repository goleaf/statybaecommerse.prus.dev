<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class RequestContext
{
    private function __construct() {}

    public static function resolveLocale(Request $request): string
    {
        /** @var list<string> $availableLocales */
        $availableLocales = TranslationService::getAvailableLocales();
        $defaultLocale = TranslationService::getDefaultLocale();

        // Start with the browser's preferred language if it matches our supported locales list.
        $preferred = null;
        $rawAcceptLanguage = $request->headers->get('Accept-Language');
        $isDefaultSymfonyAccept = is_string($rawAcceptLanguage)
            && strtolower($rawAcceptLanguage) === 'en-us,en;q=0.5';

        if ($request->hasHeader('Accept-Language') && ! $isDefaultSymfonyAccept) {
            $resolvedPreferred = $request->getPreferredLanguage($availableLocales);
            if (is_string($resolvedPreferred) && $resolvedPreferred !== '') {
                $preferred = $resolvedPreferred;
            }
        }

        // Collect all potential locale signals in priority order to mirror the SetLocale middleware behaviour.
        $candidateLocales = [];

        $routeLocale = $request->route('locale');
        if (is_string($routeLocale)) {
            $normalizedRouteLocale = trim($routeLocale);
            if ($normalizedRouteLocale !== '') {
                $candidateLocales[] = $normalizedRouteLocale;
            }
        }

        $queryLocale = $request->query('locale');
        if (is_string($queryLocale)) {
            $normalizedQueryLocale = trim($queryLocale);
            if ($normalizedQueryLocale !== '') {
                $candidateLocales[] = $normalizedQueryLocale;
            }
        }

        if (is_string($preferred)) {
            $normalizedPreferred = trim($preferred);
            if ($normalizedPreferred !== '') {
                $candidateLocales[] = $normalizedPreferred;
            }
        } elseif ($request->hasHeader('Accept-Language') && ! $isDefaultSymfonyAccept) {
            // Gracefully handle regional variants (e.g., en-GB) when getPreferredLanguage() could not resolve a direct match.
            foreach ($request->getLanguages() as $language) {
                $normalized = strtolower(str_replace('_', '-', (string) $language));
                $segment = explode('-', $normalized, 2)[0];

                if ($segment !== '') {
                    $candidateLocales[] = $segment;
                }
            }
        }

        $sessionStore = session();
        if ($sessionStore->has('locale')) {
            $sessionLocale = $sessionStore->get('locale');
            if (is_string($sessionLocale)) {
                $normalizedSessionLocale = trim($sessionLocale);
                if ($normalizedSessionLocale !== '') {
                    $candidateLocales[] = $normalizedSessionLocale;
                }
            }
        }

        if ($sessionStore->has('app.locale')) {
            $appLocale = $sessionStore->get('app.locale');
            if (is_string($appLocale)) {
                $normalizedAppLocale = trim($appLocale);
                if ($normalizedAppLocale !== '') {
                    $candidateLocales[] = $normalizedAppLocale;
                }
            }
        }

        $cookieLocale = $request->cookie('app_locale');
        if (is_string($cookieLocale)) {
            $normalizedCookieLocale = trim($cookieLocale);
            if ($normalizedCookieLocale !== '') {
                $candidateLocales[] = $normalizedCookieLocale;
            }
        }

        if (auth()->check()) {
            $preferredLocale = auth()->user()->preferred_locale ?? null;
            if (is_string($preferredLocale)) {
                $normalizedPreferredLocale = trim($preferredLocale);
                if ($normalizedPreferredLocale !== '') {
                    $candidateLocales[] = $normalizedPreferredLocale;
                }
            }
        }

        // Default to the configured locale when no candidate matches the supported list.
        $locale = $defaultLocale;

        foreach ($candidateLocales as $candidateLocale) {
            if (in_array($candidateLocale, $availableLocales, true)) {
                $locale = $candidateLocale;
                break;
            }
        }

        if (! in_array($locale, $availableLocales, true)) {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);
        app()->instance('request_locale', $locale);

        return $locale;
    }

    public static function resolveTraceId(Request $request): string
    {
        $attributeCorrelation = $request->attributes->get('correlation_id');
        if (is_string($attributeCorrelation) && $attributeCorrelation !== '') {
            return $attributeCorrelation;
        }

        if (app()->bound('request_correlation_id')) {
            $resolvedCorrelation = app()->make('request_correlation_id');
            if (is_string($resolvedCorrelation) && $resolvedCorrelation !== '') {
                return $resolvedCorrelation;
            }
        }

        $headerName = self::correlationHeader();
        $headerCorrelation = (string) $request->headers->get($headerName, '');
        if ($headerCorrelation !== '') {
            return $headerCorrelation;
        }

        $generated = Str::uuid()->toString();
        $request->attributes->set('correlation_id', $generated);
        app()->instance('request_correlation_id', $generated);

        return $generated;
    }

    public static function correlationHeader(): string
    {
        $configured = config('app.correlation_header', 'X-Correlation-ID');

        return is_string($configured) && $configured !== ''
            ? $configured
            : 'X-Correlation-ID';
    }

    public static function isApiRequest(Request $request): bool
    {
        if ($request->expectsJson() || $request->isJson()) {
            return true;
        }

        $apiPrefix = config('app.api_prefix');
        if (is_string($apiPrefix) && $apiPrefix !== '') {
            return $request->is($apiPrefix . '/*');
        }

        return $request->is('api/*');
    }
}
