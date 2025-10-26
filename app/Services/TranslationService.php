<?php

declare(strict_types=1);

namespace App\Services;

use function array_key_exists;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use function is_array;
use function is_file;

use const JSON_THROW_ON_ERROR;

use function lang_path;

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

        return self::translateUsing(
            key: $key,
            locale: $locale,
            fallbackParameters: $params,
            translator: fn (string $resolvedKey, array $parameters, string $translationLocale): string => __(
                $resolvedKey,
                $parameters,
                $translationLocale
            ),
        );
    }

    /**
     * Handle choice functionality with proper error handling.
     */
    public static function choice(string $key, int $count, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();

        return self::translateUsing(
            key: $key,
            locale: $locale,
            fallbackParameters: $params,
            translator: static fn (string $resolvedKey, array $parameters, string $translationLocale) => trans_choice(
                $resolvedKey,
                $parameters['count'] ?? 0,
                $parameters,
                $translationLocale
            ),
            additional: ['count' => $count],
        );
    }

    /**
     * Handle normalizeKey functionality with proper error handling.
     */
    public static function normalizeKey(string $key): string
    {
        if (str_contains($key, '::')) {
            [$namespace, $remainder] = explode('::', $key, 2);

            return $namespace . '::' . str_replace('.', '_', $remainder);
        }

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

    /**
     * @param callable(string, array, string):string $translator
     * @param array<string, mixed>                   $fallbackParameters
     */
    private static function translateUsing(
        string $key,
        string $locale,
        array $fallbackParameters,
        callable $translator,
        array $additional = []
    ): string {
        $parameters = array_merge($fallbackParameters, $additional);

        $translation = $translator($key, $parameters, $locale);
        if (! self::translationMissed($translation, $key)) {
            return $translation;
        }

        $normalizedKey = self::normalizeKey($key);
        if ($normalizedKey !== $key) {
            $normalizedTranslation = $translator($normalizedKey, $parameters, $locale);
            if (! self::translationMissed($normalizedTranslation, $normalizedKey)) {
                return $normalizedTranslation;
            }
        }

        $fallbackLocale = self::getFallbackLocale();
        if ($fallbackLocale !== $locale) {
            $fallbackTranslation = $translator($key, $parameters, $fallbackLocale);
            if (! self::translationMissed($fallbackTranslation, $key)) {
                return $fallbackTranslation;
            }

            if ($normalizedKey !== $key) {
                $normalizedFallbackTranslation = $translator($normalizedKey, $parameters, $fallbackLocale);
                if (! self::translationMissed($normalizedFallbackTranslation, $normalizedKey)) {
                    return $normalizedFallbackTranslation;
                }
            }

            $manualFallbackTranslation = self::fallbackTranslation($key, $parameters, $fallbackLocale);
            if ($manualFallbackTranslation !== null) {
                return $manualFallbackTranslation;
            }
        }

        $manualTranslation = self::fallbackTranslation($key, $parameters, $locale);
        if ($manualTranslation !== null) {
            return $manualTranslation;
        }

        return $translation;
    }

    private static function translationMissed(string $translation, string $expectedKey): bool
    {
        return Str::of($translation)->exactly($expectedKey);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private static function fallbackTranslation(string $key, array $parameters, string $locale): ?string
    {
        $langFile = lang_path("{$locale}.php");
        if (! is_file($langFile)) {
            return null;
        }

        /** @var mixed $messages */
        $messages = require $langFile;

        if (! is_array($messages)) {
            return null;
        }

        $lookupKeys = array_unique([$key, self::normalizeKey($key)]);

        foreach ($lookupKeys as $lookupKey) {
            if (! array_key_exists($lookupKey, $messages)) {
                continue;
            }

            $line = $messages[$lookupKey];

            if (! is_string($line)) {
                continue;
            }

            return self::makeReplacements($line, $parameters);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private static function makeReplacements(string $line, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }

            $value = (string) $value;

            $line = str_replace(
                [':' . Str::ucfirst($key), ':' . Str::upper($key), ':' . $key],
                [Str::ucfirst($value), Str::upper($value), $value],
                $line
            );
        }

        return $line;
    }
}
