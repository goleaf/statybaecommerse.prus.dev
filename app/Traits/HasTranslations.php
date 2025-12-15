<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\TranslationHookService;

trait HasTranslations
{
    /**
     * Fields that should be automatically translated
     */
    protected array $translatableFields = [
        'name', 'title', 'description', 'content', 'summary',
        'meta_title', 'meta_description', 'alt_text', 'caption'
    ];

    /**
     * Boot the trait
     */
    protected static function bootHasTranslations(): void
    {
        static::saving(function ($model) {
            $model->processTranslations();
        });
    }

    /**
     * Process translations for translatable fields
     */
    public function processTranslations(): void
    {
        $service = app(TranslationHookService::class);

        foreach ($this->getTranslatableFields() as $field) {
            if ($this->isDirty($field) && !empty($this->$field)) {
                $key = $service->generateTranslationKey(
                    $this->$field,
                    strtolower(class_basename($this))
                );

                $service->addTranslation($key, [
                    config('app.locale', 'lt') => $this->$field
                ]);

                // Store translation key if field exists
                if ($this->isFillable($field . '_translation_key')) {
                    $this->{$field . '_translation_key'} = $key;
                }
            }
        }
    }

    /**
     * Get translatable fields for this model
     */
    public function getTranslatableFields(): array
    {
        return array_intersect($this->translatableFields, $this->getFillable());
    }

    /**
     * Set translatable fields
     */
    public function setTranslatableFields(array $fields): self
    {
        $this->translatableFields = $fields;
        return $this;
    }

    /**
     * Add a translatable field
     */
    public function addTranslatableField(string $field): self
    {
        if (!in_array($field, $this->translatableFields)) {
            $this->translatableFields[] = $field;
        }
        return $this;
    }

    /**
     * Get translation for a field
     */
    public function getTranslation(string $field, string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $key = $this->getTranslationKey($field);
        
        if ($key) {
            return __($key, [], $locale);
        }

        return $this->$field;
    }

    /**
     * Get translation key for a field
     */
    public function getTranslationKey(string $field): ?string
    {
        $keyField = $field . '_translation_key';
        
        if ($this->isFillable($keyField) && !empty($this->$keyField)) {
            return $this->$keyField;
        }

        // Generate key if not stored
        if (!empty($this->$field)) {
            $service = app(TranslationHookService::class);
            return $service->generateTranslationKey(
                $this->$field,
                strtolower(class_basename($this))
            );
        }

        return null;
    }

    /**
     * Check if field has translation
     */
    public function hasTranslation(string $field, string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $key = $this->getTranslationKey($field);
        
        if (!$key) {
            return false;
        }

        $translation = __($key, [], $locale);
        return $translation !== $key; // If translation exists, it won't return the key
    }

    /**
     * Get all translations for a field
     */
    public function getAllTranslations(string $field): array
    {
        $key = $this->getTranslationKey($field);
        
        if (!$key) {
            return [];
        }

        $translations = [];
        $supportedLocales = config('app.supported_locales', 'lt,en');
        
        if (is_string($supportedLocales)) {
            $supportedLocales = array_map('trim', explode(',', $supportedLocales));
        }

        foreach ($supportedLocales as $locale) {
            $translation = __($key, [], $locale);
            if ($translation !== $key) {
                $translations[$locale] = $translation;
            }
        }

        return $translations;
    }

    /**
     * Update translation for a specific locale
     */
    public function updateTranslation(string $field, string $locale, string $translation): bool
    {
        $key = $this->getTranslationKey($field);
        
        if (!$key) {
            return false;
        }

        $service = app(TranslationHookService::class);
        return $service->addTranslation($key, [$locale => $translation]);
    }
}