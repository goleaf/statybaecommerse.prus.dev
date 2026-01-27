<?php

declare(strict_types=1);

namespace Tests\Feature\Translations;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

final class TranslationCoverageTest extends TestCase
{
    /**
     * Keep the translation coverage check focused on the app-owned translations group.
     */
    public function test_translations_group_keys_exist_in_all_locales_and_use_snake_case(): void
    {
        $locales = $this->discoverLocales();

        if ($locales === []) {
            self::markTestSkipped('No locales discovered under lang/.');
        }

        $catalogs = [];

        foreach ($locales as $locale) {
            $catalogs[$locale] = $this->loadTranslationsGroup($locale);
        }

        $keys = $this->scanTranslationKeys();
        $translationsKeys = array_values(array_filter(
            $keys,
            static fn (string $key): bool => str_starts_with($key, 'translations.')
        ));

        $invalidFormat = [];
        $missing = [];
        $defaultLocale = 'en';
        $defaultCatalog = $catalogs[$defaultLocale] ?? [];

        foreach ($translationsKeys as $key) {
            $leaf = substr($key, strlen('translations.'));

            if (! array_key_exists($leaf, $defaultCatalog)) {
                // Only enforce cross-locale coverage for keys that exist in the default locale.
                continue;
            }

            if (! preg_match('/^[a-z0-9_]+$/', $leaf)) {
                $invalidFormat[] = $key;

                continue;
            }

            foreach ($locales as $locale) {
                if (! array_key_exists($leaf, $catalogs[$locale])) {
                    $missing[$locale][] = $key;
                }
            }
        }

        $messages = [];

        if ($invalidFormat !== []) {
            $messages[] = 'Invalid translation key format: ' . implode(', ', $invalidFormat);
        }

        foreach ($missing as $locale => $keysForLocale) {
            $messages[] = sprintf(
                'Missing translations in %s: %s',
                $locale,
                implode(', ', array_values(array_unique($keysForLocale)))
            );
        }

        self::assertSame([], $messages, implode(PHP_EOL, $messages));
    }

    /**
     * @return list<string>
     */
    private function discoverLocales(): array
    {
        $langPath = base_path('lang');

        if (! is_dir($langPath)) {
            return [];
        }

        $entries = scandir($langPath);

        if ($entries === false) {
            return [];
        }

        $locales = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $langPath . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $locales[] = $entry;
            }
        }

        sort($locales);

        return array_values(array_unique($locales));
    }

    /**
     * @return array<string, mixed>
     */
    private function loadTranslationsGroup(string $locale): array
    {
        $path = base_path(sprintf('lang/%s/translations.php', $locale));

        if (! is_file($path)) {
            return [];
        }

        $data = include $path;

        return is_array($data) ? $data : [];
    }

    /**
     * @return list<string>
     */
    private function scanTranslationKeys(): array
    {
        $roots = [
            base_path('app'),
            base_path('resources'),
            base_path('routes'),
            base_path('tests'),
        ];

        $patterns = [
            '/__\(\s*[\'"]([^\'"]+)[\'"]/',
            '/trans\(\s*[\'"]([^\'"]+)[\'"]/',
            '/@lang\(\s*[\'"]([^\'"]+)[\'"]/',
        ];

        $extensions = ['php', 'blade.php', 'blade', 'js', 'ts', 'vue'];
        $keys = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                if (! in_array($extension, $extensions, true)) {
                    continue;
                }

                $content = @file_get_contents($path);

                if (! is_string($content) || $content === '') {
                    continue;
                }

                foreach ($patterns as $pattern) {
                    if (! preg_match_all($pattern, $content, $matches)) {
                        continue;
                    }

                    foreach ($matches[1] as $key) {
                        if (! is_string($key) || $key === '') {
                            continue;
                        }

                        if (str_contains($key, '$')) {
                            continue;
                        }

                        $keys[$key] = true;
                    }
                }
            }
        }

        $result = array_keys($keys);
        sort($result);

        return array_values($result);
    }
}
