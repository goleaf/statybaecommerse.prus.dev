<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Helper responsible for discovering the locales supported by the application.
 *
 * This centralises locale detection for seeds and runtime helpers so every
 * consumer pulls from the same ordered list derived from configuration or the
 * available language directories.
 */
final class Locales
{
    /**
     * Discover the supported locales declared by configuration or inferred from the lang directory.
     *
     * The logic intentionally keeps the list stable across CLI invocations so
     * seeders and console commands operate deterministically during tests.
     */
    public static function supported(): array
    {
        // Prefer the explicit configuration flag when it is available because business stakeholders
        // treat config/app.php as the canonical list that feeds storefront and admin experiences.
        $configured = config('app.supported_locales');
        $locales = [];

        if (is_string($configured)) {
            $configured = array_filter(array_map('trim', explode(',', $configured)));
        }

        if (is_array($configured) && $configured !== []) {
            $locales = $configured;
        }

        if ($locales === []) {
            // Fall back to the language directories when configuration is silent; this keeps fresh
            // test installs functioning without additional environment variables.
            $filesystem = app(Filesystem::class);
            $langPath = lang_path();

            if ($filesystem->exists($langPath)) {
                $directories = array_map(static fn (string $path): string => basename($path), $filesystem->directories($langPath));
                $jsonFiles = array_map(static fn (string $file): string => basename($file, '.json'), glob($langPath . '/*.json') ?: []);
                $locales = array_merge($directories, $jsonFiles);
            }
        }

        if ($locales === []) {
            // Ensure we never return an empty list by hydrating from the base and fallback locale
            // configuration values. The array_unique call keeps duplicates in check.
            $locales = array_filter([
                config('app.locale'),
                config('app.fallback_locale'),
            ]);
        }

        $locales = array_values(array_unique(array_filter($locales, static fn (?string $locale): bool => $locale !== null && $locale !== '')));

        // Normalise casing for safety because downstream translation lookups are case sensitive.
        return array_map(static fn (string $locale): string => Str::lower($locale), $locales);
    }
}
