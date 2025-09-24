<?php

declare(strict_types=1);

namespace App\Services\Shared;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * TranslationService
 *
 * Service class containing TranslationService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class TranslationService
{
    private const CACHE_TTL = 3600;

    // 1 hour
    private const SUPPORTED_LOCALES = ['lt', 'en', 'de'];

    /**
     * Handle getTranslation functionality with proper error handling.
     */
    public function getTranslation(string $key, mixed $localeOrReplace = null, array $replace = []): string
    {
        // Support both signatures: (key, replaceArray) or (key, locale, replaceArray)
        if (is_array($localeOrReplace)) {
            $replace = $localeOrReplace;
            $locale = app()->getLocale();
        } else {
            $locale = $localeOrReplace ?? app()->getLocale();
        }
        $cacheKey = "translation.{$locale}.{$key}";
        $translation = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $locale) {
            return $this->loadTranslationFromFiles($key, $locale);
        });
        if (! empty($replace) && is_string($translation)) {
            foreach ($replace as $search => $replacement) {
                $translation = str_replace(":{$search}", $replacement, $translation);
            }
        }

        return is_string($translation) && $translation !== '' ? $translation : $key;
    }

    /**
     * Handle getAllTranslations functionality with proper error handling.
     */
    public function getAllTranslations(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "translations.all.{$locale}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            $translations = [];
            // Load from JSON files
            $jsonFile = lang_path("{$locale}.json");
            if (File::exists($jsonFile)) {
                $jsonTranslations = json_decode(File::get($jsonFile), true) ?? [];
                $translations = array_merge($translations, $jsonTranslations);
            }
            // Load from PHP files
            $phpFiles = File::glob(lang_path("{$locale}/*.php"));
            foreach ($phpFiles as $file) {
                $group = pathinfo($file, PATHINFO_FILENAME);
                $groupTranslations = include $file;
                if (is_array($groupTranslations)) {
                    foreach ($groupTranslations as $key => $value) {
                        $translations["{$group}.{$key}"] = $value;
                    }
                }
            }

            return $translations;
        });
    }

    /**
     * Handle clearTranslationCache functionality with proper error handling.
     */
    public function clearTranslationCache(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translations.all.{$locale}");
            Cache::flush();
            // Clear individual translation keys
        } else {
            foreach (self::SUPPORTED_LOCALES as $loc) {
                Cache::forget("translations.all.{$loc}");
            }
            Cache::flush();
        }
    }

    /**
     * Handle getSupportedLocales functionality with proper error handling.
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Handle isLocaleSupported functionality with proper error handling.
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES);
    }

    /**
     * Handle getDefaultLocale functionality with proper error handling.
     */
    public function getDefaultLocale(): string
    {
        return 'lt';
        // Lithuanian as default per rules
    }

    /**
     * Handle getCurrentCurrency functionality with proper error handling.
     */
    public function getCurrentCurrency(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'lt' => 'EUR',
            'en' => 'EUR',
            // Euro for all locales per rules
            'de' => 'EUR',
            default => 'EUR',
        };
    }

    /**
     * Handle loadTranslationFromFiles functionality with proper error handling.
     */
    private function loadTranslationFromFiles(string $key, string $locale): string|array|null
    {
        // Try JSON file first
        $jsonFile = lang_path("{$locale}.json");
        if (File::exists($jsonFile)) {
            $translations = json_decode(File::get($jsonFile), true) ?? [];
            if (isset($translations[$key])) {
                return $translations[$key];
            }
        }
        // Try PHP files with dot notation
        if (str_contains($key, '.')) {
            [$group, $item] = explode('.', $key, 2);
            $phpFile = lang_path("{$locale}/{$group}.php");
            if (File::exists($phpFile)) {
                $translations = include $phpFile;

                return data_get($translations, $item);
            }
        }

        return null;
    }
}
