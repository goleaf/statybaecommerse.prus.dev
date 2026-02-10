<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class TranslationHookService
{
    private array $supportedLocales;

    private string $defaultLocale;

    public function __construct()
    {
        $this->supportedLocales = $this->getSupportedLocales();
        $this->defaultLocale = config('app.locale', 'lt');
    }

    /**
     * Add or update a translation key across all supported locales.
     * strictly for UI/static translations in lang files.
     */
    public function addTranslation(string $key, array $translations): bool
    {
        try {
            foreach ($this->supportedLocales as $locale) {
                $translation = $translations[$locale] ?? $translations[$this->defaultLocale] ?? $key;
                $this->updateTranslationFile($locale, $key, $translation);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Translation hook failed', [
                'key'          => $key,
                'translations' => $translations,
                'error'        => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Auto-generate translation key from text.
     */
    public function generateTranslationKey(string $text, string $prefix = ''): string
    {
        $key = Str::snake(Str::lower($text));
        $key = preg_replace('/[^a-z0-9_]/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        $key = trim($key, '_');

        if ($prefix) {
            $key = $prefix . '.' . $key;
        }

        return $key;
    }

    /**
     * Detect and extract translatable strings from Blade files.
     */
    public function extractTranslatableStrings(string $content): array
    {
        $patterns = [
            '/\{\{\s*__\([\'"]([^\'"]+)[\'"]\)\s*\}\}/',
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',
            '/trans\([\'"]([^\'"]+)[\'"]\)/',
            '/__\([\'"]([^\'"]+)[\'"]\)/',
        ];

        $keys = [];
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (! empty($matches[1])) {
                $keys = array_merge($keys, $matches[1]);
            }
        }

        return array_unique($keys);
    }

    /**
     * Process Blade file and ensure all translations exist.
     */
    public function processBladeFile(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $keys = $this->extractTranslatableStrings($content);
        $missingKeys = [];

        foreach ($keys as $key) {
            if (! $this->translationExists($key)) {
                $missingKeys[] = $key;
                $this->addTranslation($key, [
                    $this->defaultLocale => $this->humanizeKey($key),
                ]);
            }
        }

        return $missingKeys;
    }

    /**
     * Get missing translations for a specific locale.
     */
    public function getMissingTranslations(string $locale): array
    {
        $defaultTranslations = $this->loadAllTranslations($this->defaultLocale);
        $localeTranslations = $this->loadAllTranslations($locale);

        return array_diff_key($defaultTranslations, $localeTranslations);
    }

    /**
     * Generate translation report.
     */
    public function generateTranslationReport(): array
    {
        $defaultTranslations = $this->loadAllTranslations($this->defaultLocale);
        $report = [
            'total_keys' => count($defaultTranslations),
            'locales'    => [],
        ];

        foreach ($this->supportedLocales as $locale) {
            $translations = $this->loadAllTranslations($locale);
            $missing = $this->getMissingTranslations($locale);

            $report['locales'][$locale] = [
                'translated'            => count($translations),
                'missing'               => count($missing),
                'completion_percentage' => $report['total_keys'] > 0
                    ? round((count($translations) / $report['total_keys']) * 100, 2)
                    : 0,
                'missing_keys' => array_keys($missing),
            ];
        }

        return $report;
    }

    private function getSupportedLocales(): array
    {
        $locales = config('app.supported_locales', 'lt,en');

        if (is_string($locales)) {
            return array_map('trim', explode(',', $locales));
        }

        return is_array($locales) ? $locales : ['lt', 'en'];
    }

    private function updateTranslationFile(string $locale, string $key, string $translation): void
    {
        $parts = explode('.', $key, 2);
        $group = count($parts) === 2 ? $parts[0] : 'messages';
        $subKey = count($parts) === 2 ? $parts[1] : $key;

        $phpFile = lang_path("{$locale}/{$group}.php");
        $translations = [];

        if (File::exists($phpFile)) {
            $translations = (static function ($f) {
                return include $f;
            })($phpFile);
            if (! is_array($translations)) {
                $translations = [];
            }
        }

        if (isset($translations[$subKey]) && $translations[$subKey] === $translation) {
            return;
        }

        $translations[$subKey] = $translation;
        ksort($translations);

        $this->savePhpTranslationFile($locale, $group, $translations);
    }

    private function savePhpTranslationFile(string $locale, string $group, array $translations): void
    {
        $localeDir = lang_path($locale);
        if (! File::isDirectory($localeDir)) {
            File::makeDirectory($localeDir, 0755, true);
        }

        $phpFile = "{$localeDir}/{$group}.php";
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($translations, true) . ";\n";
        File::put($phpFile, $content);
    }

    private function loadAllTranslations(string $locale): array
    {
        $translations = [];
        $localeDir = lang_path($locale);

        if (File::isDirectory($localeDir)) {
            $files = File::files($localeDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $group = $file->getFilenameWithoutExtension();
                    $fileTranslations = (static function ($f) {
                        return include $f;
                    })($file->getPathname());
                    if (is_array($fileTranslations)) {
                        foreach ($fileTranslations as $k => $v) {
                            $translations["{$group}.{$k}"] = $v;
                        }
                    }
                }
            }
        }

        return $translations;
    }

    private function translationExists(string $key): bool
    {
        $parts = explode('.', $key, 2);
        $group = count($parts) === 2 ? $parts[0] : 'messages';
        $subKey = count($parts) === 2 ? $parts[1] : $key;

        $phpFile = lang_path("{$this->defaultLocale}/{$group}.php");
        if (! File::exists($phpFile)) {
            return false;
        }

        $translations = (static function ($f) {
            return include $f;
        })($phpFile);

        return is_array($translations) && isset($translations[$subKey]);
    }

    private function humanizeKey(string $key): string
    {
        return Str::title(str_replace(['_', '.'], ' ', $key));
    }
}