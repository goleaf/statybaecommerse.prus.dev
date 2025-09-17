<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * HasTranslations
 * 
 * Trait providing reusable functionality across multiple classes.
 */
trait HasTranslations
{
    public function translations(): HasMany
    {
        $translationModel = $this->translationModelClass();
        $foreignKey = $this->getForeignKey();
        return $this->hasMany($translationModel, $foreignKey);
    }
    public function trans(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?: app()->getLocale();
        // Load translations if not already loaded
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }
        $translation = $this->translations->firstWhere('locale', $locale);
        if ($translation && isset($translation->{$field}) && !empty($translation->{$field})) {
            $value = $translation->{$field};
            if (is_array($value)) {
                return $value[$locale]
                    ?? reset($value)
                    ?? ($this->{$field} ?? null);
            }
            return $value;
        }

        $defaultLocale = config('app.locale', 'en');

        // Fallback to default locale if current locale not found
        if ($locale !== $defaultLocale) {
            $defaultTranslation = $this->translations->firstWhere('locale', $defaultLocale);
            if ($defaultTranslation && isset($defaultTranslation->{$field}) && !empty($defaultTranslation->{$field})) {
                $value = $defaultTranslation->{$field};
                if (is_array($value)) {
                    return $value[$defaultLocale]
                        ?? ($value[$locale] ?? null)
                        ?? reset($value);
                }
                return $value;
            }
        }

        $value = $this->{$field} ?? null;

        if (is_array($value)) {
            return $value[$locale]
                ?? ($value[$defaultLocale] ?? null)
                ?? reset($value);
        }

        return $value;
    }
    protected function translationModelClass(): string
    {
        // Expect model to define translation model via property
        if (property_exists($this, 'translationModel')) {
            return $this->translationModel;
        }
        throw new \RuntimeException(static::class . ' must define $translationModel to use HasTranslations');
    }
}
