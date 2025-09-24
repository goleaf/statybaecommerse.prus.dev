<?php

declare(strict_types=1);

namespace App\Services;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Schemas\SimpleTabSchema;

/**
 * MultiLanguageTabService
 *
 * Service class containing MultiLanguageTabService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class MultiLanguageTabService
{
    /**
     * Handle getAvailableLanguages functionality with proper error handling.
     */
    public static function getAvailableLanguages(): array
    {
        $supported = config('app.supported_locales', ['lt', 'en']);
        $locales = is_array($supported) ? $supported : array_filter(array_map('trim', explode(',', (string) $supported)));
        $locales = array_values(array_filter(array_map('trim', $locales)));

        return collect($locales)->map(function (string $locale) {
            return ['code' => $locale, 'name' => self::getLanguageName($locale), 'flag' => self::getLanguageFlag($locale)];
        })->toArray();
    }

    /**
     * Handle getLanguageName functionality with proper error handling.
     */
    public static function getLanguageName(string $locale): string
    {
        return match ($locale) {
            'en' => __('English'),
            'lt' => __('Lietuvių'),
            'de' => __('Deutsch'),
            'fr' => __('Français'),
            'es' => __('Español'),
            'ru' => __('Русский'),
            default => strtoupper($locale),
        };
    }

    /**
     * Handle getLanguageFlag functionality with proper error handling.
     */
    public static function getLanguageFlag(string $locale): string
    {
        return match ($locale) {
            'en' => '🇬🇧',
            'lt' => '🇱🇹',
            'de' => '🇩🇪',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'ru' => '🇷🇺',
            default => '🌐',
        };
    }

    /**
     * Handle createSimpleTabs functionality with proper error handling.
     */
    public static function createSimpleTabs(array $fields): array
    {
        $languages = self::getAvailableLanguages();
        $tabs = [];
        foreach ($languages as $language) {
            $tabFields = [];
            foreach ($fields as $field => $config) {
                $tabFields[] = match ($config['type'] ?? 'text') {
                    'text' => TextInput::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->maxLength($config['maxLength'] ?? 255),
                    'textarea' => Textarea::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->rows($config['rows'] ?? 3)->maxLength($config['maxLength'] ?? 1000),
                    'rich_editor' => RichEditor::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->toolbarButtons($config['toolbar'] ?? ['bold', 'italic', 'link', 'bulletList', 'orderedList']),
                    default => TextInput::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false),
                };
            }
            $tabs[] = TabLayoutTab::make($language['name'])->id("tab-{$language['code']}")->icon('heroicon-o-language')->badge($language['flag'])->schema($tabFields);
        }

        return $tabs;
    }

    /**
     * Handle createAdvancedTabs functionality with proper error handling.
     */
    public static function createAdvancedTabs(callable $schemaBuilder): array
    {
        $languages = self::getAvailableLanguages();
        $tabs = [];
        foreach ($languages as $language) {
            $schema = $schemaBuilder($language['code'], $language);
            $components = is_array($schema) ? $schema : [$schema];
            $tabs[] = TabLayoutTab::make($language['name'])->id("tab-{$language['code']}")->icon('heroicon-o-language')->badge($language['flag'])->schema($components);
        }

        return $tabs;
    }

    /**
     * Handle createSectionedTabs functionality with proper error handling.
     */
    public static function createSectionedTabs(array $sections): array
    {
        $languages = self::getAvailableLanguages();
        $tabs = [];
        foreach ($languages as $language) {
            $sectionComponents = [];
            foreach ($sections as $sectionName => $fields) {
                $sectionFields = [];
                foreach ($fields as $field => $config) {
                    $sectionFields[] = match ($config['type'] ?? 'text') {
                        'text' => TextInput::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->maxLength($config['maxLength'] ?? 255)->placeholder($config['placeholder'] ?? ''),
                        'textarea' => Textarea::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->rows($config['rows'] ?? 3)->maxLength($config['maxLength'] ?? 1000)->placeholder($config['placeholder'] ?? ''),
                        'rich_editor' => RichEditor::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false)->toolbarButtons($config['toolbar'] ?? ['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3']),
                        default => TextInput::make("{$field}_{$language['code']}")->label($config['label'] ?? ucfirst($field))->required($config['required'] ?? false),
                    };
                }
                $sectionComponents[] = Section::make(__("translations.{$sectionName}"))->schema($sectionFields);
            }
            $tabs[] = TabLayoutTab::make($language['name'])->id("tab-{$language['code']}")->icon('heroicon-o-language')->badge($language['flag'])->schema($sectionComponents);
        }

        return $tabs;
    }

    /**
     * Handle createSimpleTabSchemas functionality with proper error handling.
     */
    public static function createSimpleTabSchemas(string $componentClass, array $data = []): array
    {
        $languages = self::getAvailableLanguages();
        $schemas = [];
        foreach ($languages as $language) {
            $schemas[] = SimpleTabSchema::make(label: $language['name'], id: "simple-tab-{$language['code']}")->livewireComponent($componentClass, array_merge($data, ['locale' => $language['code']]))->icon('heroicon-o-language')->badge($language['flag']);
        }

        return $schemas;
    }

    /**
     * Handle getDefaultActiveTab functionality with proper error handling.
     */
    public static function getDefaultActiveTab(): int
    {
        $languages = self::getAvailableLanguages();
        // Find Lithuanian tab index
        foreach ($languages as $index => $language) {
            if ($language['code'] === 'lt') {
                return $index;
            }
        }

        // Fallback to first tab
        return 0;
    }

    /**
     * Handle prepareTranslationData functionality with proper error handling.
     */
    public static function prepareTranslationData(array $formData, array $translatableFields): array
    {
        $languages = collect(self::getAvailableLanguages())->pluck('code');
        $translations = [];
        foreach ($languages as $locale) {
            $translations[$locale] = [];
            foreach ($translatableFields as $field) {
                $fieldKey = "{$field}_{$locale}";
                if (isset($formData[$fieldKey])) {
                    $translations[$locale][$field] = $formData[$fieldKey];
                    unset($formData[$fieldKey]);
                    // Remove from main form data
                }
            }
        }

        return ['main_data' => $formData, 'translations' => $translations];
    }

    /**
     * Handle populateFormWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $record
     */
    public static function populateFormWithTranslations($record, array $translatableFields): array
    {
        if (! $record || ! method_exists($record, 'translations')) {
            return [];
        }
        $languages = collect(self::getAvailableLanguages())->pluck('code');
        $formData = [];
        foreach ($languages as $locale) {
            $translation = $record->translations->where('locale', $locale)->first();
            if ($translation) {
                foreach ($translatableFields as $field) {
                    $formData["{$field}_{$locale}"] = $translation->{$field} ?? '';
                }
            }
        }

        return $formData;
    }
}
