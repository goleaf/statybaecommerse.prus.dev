<?php declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\App;

final class TranslationService
{
    /**
     * Get translation using snake_case keys from unified language files
     */
    public static function get(string $key, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        $normalizedKey = self::normalizeKey($key);
        
        return __($normalizedKey, $params, $locale);
    }

    /**
     * Get translation with pluralization
     */
    public static function choice(string $key, int $count, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        $normalizedKey = self::normalizeKey($key);
        
        return trans_choice($normalizedKey, $count, $params, $locale);
    }

    /**
     * Convert dot notation to snake_case
     */
    public static function normalizeKey(string $key): string
    {
        return str_replace('.', '_', $key);
    }

    /**
     * Get all available locales from config
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
     * Check if locale is supported
     */
    public static function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, self::getAvailableLocales(), true);
    }

    /**
     * Get default locale
     */
    public static function getDefaultLocale(): string
    {
        return config('app.locale', 'lt');
    }

    /**
     * Get fallback locale
     */
    public static function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }
}
