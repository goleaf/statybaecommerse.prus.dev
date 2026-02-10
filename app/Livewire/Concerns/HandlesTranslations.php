<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

/**
 * Trait HandlesTranslations
 * 
 * Refactored to avoid dynamic generation of translation files.
 * Static translations should be managed manually in /lang files.
 */
trait HandlesTranslations
{
    /**
     * Properties that should be automatically translated
     */
    protected array $translatableProperties = [];

    /**
     * Get translatable properties
     */
    public function getTranslatableProperties(): array
    {
        return $this->translatableProperties;
    }

    /**
     * Set translatable properties
     */
    public function setTranslatableProperties(array $properties): void
    {
        $this->translatableProperties = $properties;
    }

    /**
     * Add a translatable property
     */
    public function addTranslatableProperty(string $property): void
    {
        if (! in_array($property, $this->translatableProperties)) {
            $this->translatableProperties[] = $property;
        }
    }

    /**
     * Process translations for component properties - DISABLED
     */
    public function processComponentTranslations(): void
    {
        // Dynamic translation file generation disabled per codebase policy.
    }

    /**
     * Get translation for a property
     */
    public function getPropertyTranslation(string $property, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        if (! isset($this->$property) || empty($this->$property)) {
            return null;
        }

        // If it's a model that has the trans method, use it
        if (is_object($this->$property) && method_exists($this->$property, 'trans')) {
            return $this->$property->trans($property, $locale);
        }

        return $this->$property;
    }

    /**
     * Update property translation - DISABLED
     */
    public function updatePropertyTranslation(string $property, string $locale, string $translation): bool
    {
        return false;
    }

    /**
     * Livewire hook - DISABLED
     */
    public function dehydrate(): void
    {
        // Automatic translation discovery disabled.
    }

    /**
     * Helper method to translate text on the fly - DISABLED
     */
    public function translateText(string $text, ?array $locales = null): array
    {
        return [];
    }

    /**
     * Get supported locales
     */
    private function getSupportedLocales(): array
    {
        $locales = config('app.supported_locales', 'lt,en');

        if (is_string($locales)) {
            return array_map('trim', explode(',', $locales));
        }

        return is_array($locales) ? $locales : ['lt', 'en'];
    }
}