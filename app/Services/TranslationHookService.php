<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class TranslationHookService
{
    private array $supportedLocales;
    private string $defaultLocale;
    private array $translationFiles = [];

    public function __construct()
    {
        $this->supportedLocales = $this->getSupportedLocales();
        $this->defaultLocale = config('app.locale', 'lt');
        $this->loadTranslationFiles();
    }

    /**
     * Add or update a translation key across all supported locales
     */
    public function addTranslation(string $key, array $translations): bool
    {
        try {
            foreach ($this->supportedLocales as $locale) {
                $translation = $translations[$locale] ?? $translations[$this->defaultLocale] ?? $key;
                $this->updateTranslationFile($locale, $key, $translation);
            }

            $this->saveTranslationFiles();
            return true;
        } catch (\Exception $e) {
            Log::error('Translation hook failed', [
                'key' => $key,
                'translations' => $translations,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Auto-generate translation key from text
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
     * Detect and extract translatable strings from Blade files
     */
    public function extractTranslatableStrings(string $content): array
    {
        $patterns = [
            // {{ __('key') }}
            '/\{\{\s*__\([\'"]([^\'"]+)[\'"]\)\s*\}\}/',
            // @lang('key')
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',
            // trans('key')
            '/trans\([\'"]([^\'"]+)[\'"]\)/',
            // __('key')
            '/__\([\'"]([^\'"]+)[\'"]\)/',
        ];

        $keys = [];
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (!empty($matches[1])) {
                $keys = array_merge($keys, $matches[1]);
            }
        }

        return array_unique($keys);
    }

    /**
     * Process Blade file and ensure all translations exist
     */
    public function processBladeFile(string $filePath): array
    {
        if (!File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $keys = $this->extractTranslatableStrings($content);
        $missingKeys = [];

        foreach ($keys as $key) {
            if (!$this->translationExists($key)) {
                $missingKeys[] = $key;
                // Auto-create translation with key as default value
                $this->addTranslation($key, [
                    $this->defaultLocale => $this->humanizeKey($key)
                ]);
            }
        }

        return $missingKeys;
    }

    /**
     * Hook into model events to auto-translate model attributes
     */
    public function hookModelTranslations(string $modelClass, array $translatableFields): void
    {
        $modelClass::saving(function ($model) use ($translatableFields) {
            foreach ($translatableFields as $field) {
                if ($model->isDirty($field) && !empty($model->$field)) {
                    $key = $this->generateTranslationKey($model->$field, strtolower(class_basename($model)));
                    
                    // Create translation entry
                    $this->addTranslation($key, [
                        $this->defaultLocale => $model->$field
                    ]);

                    // Store the translation key for reference
                    $model->{$field . '_translation_key'} = $key;
                }
            }
        });
    }

    /**
     * Sync translations between different formats (JSON and PHP arrays)
     */
    public function syncTranslationFormats(): void
    {
        foreach ($this->supportedLocales as $locale) {
            $jsonFile = lang_path("{$locale}.json");
            $phpFile = lang_path("{$locale}.php");

            if (File::exists($jsonFile)) {
                $jsonTranslations = json_decode(File::get($jsonFile), true) ?? [];
                
                if (File::exists($phpFile)) {
                    $phpTranslations = include $phpFile;
                    if (is_array($phpTranslations)) {
                        // Merge JSON translations into PHP format
                        $merged = array_merge($phpTranslations, $jsonTranslations);
                        $this->savePhpTranslationFile($locale, $merged);
                    }
                }
            }
        }
    }

    /**
     * Get missing translations for a specific locale
     */
    public function getMissingTranslations(string $locale): array
    {
        $defaultTranslations = $this->translationFiles[$this->defaultLocale] ?? [];
        $localeTranslations = $this->translationFiles[$locale] ?? [];

        return array_diff_key($defaultTranslations, $localeTranslations);
    }

    /**
     * Generate translation report
     */
    public function generateTranslationReport(): array
    {
        $report = [
            'total_keys' => count($this->translationFiles[$this->defaultLocale] ?? []),
            'locales' => []
        ];

        foreach ($this->supportedLocales as $locale) {
            $translations = $this->translationFiles[$locale] ?? [];
            $missing = $this->getMissingTranslations($locale);
            
            $report['locales'][$locale] = [
                'translated' => count($translations),
                'missing' => count($missing),
                'completion_percentage' => $report['total_keys'] > 0 
                    ? round((count($translations) / $report['total_keys']) * 100, 2) 
                    : 0,
                'missing_keys' => array_keys($missing)
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

    private function loadTranslationFiles(): void
    {
        foreach ($this->supportedLocales as $locale) {
            $jsonFile = lang_path("{$locale}.json");
            
            if (File::exists($jsonFile)) {
                $content = File::get($jsonFile);
                $this->translationFiles[$locale] = json_decode($content, true) ?? [];
            } else {
                $this->translationFiles[$locale] = [];
            }
        }
    }

    private function updateTranslationFile(string $locale, string $key, string $translation): void
    {
        if (!isset($this->translationFiles[$locale])) {
            $this->translationFiles[$locale] = [];
        }

        $this->translationFiles[$locale][$key] = $translation;
    }

    private function saveTranslationFiles(): void
    {
        foreach ($this->translationFiles as $locale => $translations) {
            $jsonFile = lang_path("{$locale}.json");
            
            // Sort translations alphabetically
            ksort($translations);
            
            $jsonContent = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            File::put($jsonFile, $jsonContent);
        }
    }

    private function savePhpTranslationFile(string $locale, array $translations): void
    {
        $phpFile = lang_path("{$locale}.php");
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        File::put($phpFile, $content);
    }

    private function translationExists(string $key): bool
    {
        return isset($this->translationFiles[$this->defaultLocale][$key]);
    }

    private function humanizeKey(string $key): string
    {
        return Str::title(str_replace(['_', '.'], ' ', $key));
    }
}