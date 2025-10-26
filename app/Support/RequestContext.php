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
        $availableLocales = TranslationService::getAvailableLocales();
        $preferred = $request->getPreferredLanguage($availableLocales);
        $currentLocale = app()->getLocale();

        $locale = is_string($preferred) && $preferred !== ''
            ? $preferred
            : ($currentLocale !== ''
                ? $currentLocale
                : TranslationService::getDefaultLocale());

        if (! in_array($locale, $availableLocales, true)) {
            $locale = TranslationService::getDefaultLocale();
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
