<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Services\TranslationHookService;

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
     * Process translations for component properties
     */
    public function processComponentTranslations(): void
    {
        $service = app(TranslationHookService::class);

        foreach ($this->translatableProperties as $property) {
            if (isset($this->$property) && ! empty($this->$property)) {
                $key = $service->generateTranslationKey(
                    $this->$property,
                    strtolower(class_basename($this))
                );

                $service->addTranslation($key, [
                    config('app.locale', 'lt') => $this->$property,
                ]);
            }
        }
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

        $service = app(TranslationHookService::class);
        $key = $service->generateTranslationKey(
            $this->$property,
            strtolower(class_basename($this))
        );

        $translation = __($key, [], $locale);

        return $translation !== $key ? $translation : $this->$property;
    }

    /**
     * Update property translation
     */
    public function updatePropertyTranslation(string $property, string $locale, string $translation): bool
    {
        if (! isset($this->$property)) {
            return false;
        }

        $service = app(TranslationHookService::class);
        $key = $service->generateTranslationKey(
            $this->$property,
            strtolower(class_basename($this))
        );

        return $service->addTranslation($key, [$locale => $translation]);
    }

    /**
     * Livewire hook - called when component is dehydrated
     */
    public function dehydrate(): void
    {
        if (! empty($this->translatableProperties)) {
            $this->processComponentTranslations();
        }
    }

    /**
     * Helper method to translate text on the fly
     */
    public function translateText(string $text, ?array $locales = null): array
    {
        $service = app(TranslationHookService::class);
        $key = $service->generateTranslationKey($text, 'component');

        $locales = $locales ?? $this->getSupportedLocales();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = $locale === config('app.locale', 'lt') ? $text : $text;
        }

        $service->addTranslation($key, $translations);

        return $translations;
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
