<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\App;

/**
 * TranslationService
 *
 * Service class containing TranslationService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class TranslationService
{
    /**
     * Handle get functionality with proper error handling.
     */
    public static function get(string $key, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        $normalizedKey = self::normalizeKey($key);

        return __($normalizedKey, $params, $locale);
    }

    /**
     * Handle choice functionality with proper error handling.
     */
    public static function choice(string $key, int $count, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        $normalizedKey = self::normalizeKey($key);

        return trans_choice($normalizedKey, $count, $params, $locale);
    }

    /**
     * Handle normalizeKey functionality with proper error handling.
     */
    public static function normalizeKey(string $key): string
    {
        return str_replace('.', '_', $key);
    }

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public static function getAvailableLocales(): array
    {
        $supported = config('app.supported_locales', 'lt,en');
        if (is_array($supported)) {
            return $supported;
        }

        return array_map('trim', explode(',', $supported));
    }

    /**
     * Handle isLocaleSupported functionality with proper error handling.
     */
    public static function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, self::getAvailableLocales(), true);
    }

    /**
     * Handle getDefaultLocale functionality with proper error handling.
     */
    public static function getDefaultLocale(): string
    {
        return config('app.locale', 'lt');
    }

    /**
     * Handle getFallbackLocale functionality with proper error handling.
     */
    public static function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }
}
