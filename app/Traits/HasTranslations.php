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
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }
        $translation = $this->translations->firstWhere('locale', $locale);
        // If relation was loaded earlier and now a new locale was added, fetch it fresh
        if (! $translation) {
            $fresh = $this->translations()->where('locale', $locale)->first();
            if ($fresh) {
                // Merge freshly fetched translation into the loaded relation to keep cache coherent
                $this->setRelation('translations', $this->translations->push($fresh));
                $translation = $fresh;
            }
        }
        if ($translation && isset($translation->{$field}) && ! empty($translation->{$field})) {
            $value = $translation->{$field};
            if (is_array($value)) {
                return $value[$locale] ?? reset($value) ?? $this->{$field} ?? null;
            }

            return $value;
        }
        // Fallback to default locale if current locale not found
        $defaultLocale = config('app.locale', 'en');
        if ($locale !== $defaultLocale) {
            $defaultTranslation = $this->translations->firstWhere('locale', $defaultLocale);
            if ($defaultTranslation && isset($defaultTranslation->{$field}) && ! empty($defaultTranslation->{$field})) {
                return $defaultTranslation->{$field};
            }
        }

        return $this->getAttributes()[$field] ?? null;
    }

    public function getTranslation(string $field, ?string $locale = null, bool $useFallbackLocale = true): mixed
    {
        $locale ??= app()->getLocale();

        $value = $this->translations()
            ->where('locale', $locale)
            ->value($field);

        if ($value !== null) {
            return $value;
        }

        if ($useFallbackLocale) {
            $fallback = config('app.fallback_locale', 'en');

            if ($fallback !== $locale) {
                return $this->getTranslation($field, $fallback, false);
            }
        }

        return $this->getAttributes()[$field] ?? null;
    }

    public function getTranslation(string $field, string $locale, bool $useFallbackLocale = true): mixed
    {
        $value = $this->trans($field, $locale);

        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($useFallbackLocale) {
            $fallbackLocale = config('app.fallback_locale', config('app.locale'));

            if ($fallbackLocale && $fallbackLocale !== $locale) {
                $fallbackValue = $this->trans($field, $fallbackLocale);

                if ($fallbackValue !== null && $fallbackValue !== '') {
                    return $fallbackValue;
                }
            }
        }

        return $this->getAttributeFromArray($field) ?? null;
    }

    protected function translationModelClass(): string
    {
        // Expect model to define translation model via property
        if (property_exists($this, 'translationModel')) {
            return $this->translationModel;
        }
        throw new \RuntimeException(static::class.' must define $translationModel to use HasTranslations');
    }
}
