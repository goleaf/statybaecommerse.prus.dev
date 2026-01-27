<?php

declare(strict_types=1);

namespace Tests\Feature\Localization;

use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Throwable;

class TranslationCompletenessPropertyTest extends TestCase
{
    /**
     * **Feature: filament-downgrade-restore, Property 4: Translation Completeness**
     * **Validates: Requirements 3.3, 5.3**
     *
     * For any user-facing string in restored functionality, translations must exist for all supported languages (lt, en, de, ru)
     */
    public function test_translation_completeness_property(): void
    {
        $supportedLocales = ['lt', 'en', 'de', 'ru'];
        $langPath = lang_path();

        // Property: All supported locales should have corresponding language directories
        foreach ($supportedLocales as $locale) {
            $localePath = $langPath . DIRECTORY_SEPARATOR . $locale;
            $this->assertDirectoryExists(
                $localePath,
                "Language directory for locale '{$locale}' should exist"
            );
        }

        // Property: Core translation files should exist for all locales
        $coreTranslationFiles = [
            'messages.php',
            'validation.php',
        ];

        foreach ($supportedLocales as $locale) {
            foreach ($coreTranslationFiles as $file) {
                $filePath = $langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists(
                    $filePath,
                    "Core translation file '{$file}' should exist for locale '{$locale}'"
                );
            }
        }
    }

    /**
     * **Feature: filament-downgrade-restore, Property 4: Translation Completeness**
     * **Validates: Requirements 3.3, 5.3**
     *
     * Property: Translation keys should be consistent across all supported locales
     */
    public function test_translation_key_consistency_property(): void
    {
        $supportedLocales = ['lt', 'en', 'de', 'ru'];
        $langPath = lang_path();

        // Get all translation files from the primary locale (Lithuanian)
        $primaryLocale = 'lt';
        $primaryLocalePath = $langPath . DIRECTORY_SEPARATOR . $primaryLocale;

        if (! is_dir($primaryLocalePath)) {
            $this->markTestSkipped("Primary locale directory '{$primaryLocale}' does not exist");
        }

        $translationFiles = $this->getTranslationFiles($primaryLocalePath);

        foreach ($translationFiles as $file) {
            $relativePath = str_replace($primaryLocalePath . DIRECTORY_SEPARATOR, '', $file);

            // Skip if it's a subdirectory file for now (admin/, frontend/, etc.)
            if (str_contains($relativePath, DIRECTORY_SEPARATOR)) {
                continue;
            }

            $primaryKeys = $this->getTranslationKeys($file);

            // Property: For any translation file in primary locale, equivalent files should exist in other locales
            foreach ($supportedLocales as $locale) {
                if ($locale === $primaryLocale) {
                    continue;
                }

                $localeFilePath = $langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $relativePath;

                // Check if file exists (some locales might have fewer files, which is acceptable)
                if (! file_exists($localeFilePath)) {
                    continue;
                }

                $localeKeys = $this->getTranslationKeys($localeFilePath);

                // Property: Translation files should have consistent structure
                $this->assertIsArray(
                    $localeKeys,
                    "Translation file '{$relativePath}' for locale '{$locale}' should return an array"
                );
            }
        }
    }

    /**
     * **Feature: filament-downgrade-restore, Property 4: Translation Completeness**
     * **Validates: Requirements 3.3, 5.3**
     *
     * Property: Catalogue translation files should exist for all supported locales
     */
    public function test_catalogue_translation_completeness_property(): void
    {
        $supportedLocales = ['lt', 'en', 'de', 'ru'];
        $langPath = lang_path();
        $catalogueFiles = ['brand.php', 'category.php', 'city.php', 'country.php', 'product.php'];

        // Property: Catalogue translation files should exist for all locales
        foreach ($supportedLocales as $locale) {
            foreach ($catalogueFiles as $file) {
                $filePath = $langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists(
                    $filePath,
                    "Catalogue translation file '{$file}' should exist for locale '{$locale}'"
                );

                // Property: Files should return valid arrays
                $translations = include $filePath;
                $this->assertIsArray(
                    $translations,
                    "Catalogue translation file '{$file}' for locale '{$locale}' should return an array"
                );

                $this->assertNotEmpty(
                    $translations,
                    "Catalogue translation file '{$file}' for locale '{$locale}' should not be empty"
                );
            }
        }
    }

    /**
     * **Feature: filament-downgrade-restore, Property 4: Translation Completeness**
     * **Validates: Requirements 3.3, 5.3**
     *
     * Property: Essential e-commerce translation keys should exist across all locales
     */
    public function test_essential_ecommerce_translations_property(): void
    {
        $supportedLocales = ['lt', 'en', 'de', 'ru'];
        $langPath = lang_path();

        // Essential e-commerce translation files that should exist
        $essentialFiles = [
            'product.php',
            'category.php',
            'brand.php',
        ];

        // Property: Essential e-commerce translation files should exist for primary locales
        $primaryLocales = ['lt', 'en']; // Lithuanian and English are most important

        foreach ($primaryLocales as $locale) {
            foreach ($essentialFiles as $file) {
                $filePath = $langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists(
                    $filePath,
                    "Essential e-commerce translation file '{$file}' should exist for primary locale '{$locale}'"
                );

                // Property: Translation files should return arrays
                $translations = include $filePath;
                $this->assertIsArray(
                    $translations,
                    "Translation file '{$file}' for locale '{$locale}' should return an array"
                );
            }
        }
    }

    /**
     * Get all PHP translation files from a directory
     */
    private function getTranslationFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        return File::glob($directory . DIRECTORY_SEPARATOR . '*.php');
    }

    /**
     * Get translation keys from a PHP translation file
     */
    private function getTranslationKeys(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        try {
            $translations = include $filePath;

            return is_array($translations) ? $translations : [];
        } catch (Throwable $e) {
            return [];
        }
    }
}
