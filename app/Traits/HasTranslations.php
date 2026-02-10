<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslations
{
    /**
     * Get translated field value for the specified locale.
     * strictly uses database translations to avoid filesystem dependencies for dynamic content.
     */
    public function trans(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();

        // If translations are loaded, use them to avoid additional queries
        if ($this->relationLoaded('translations')) {
            $translation = $this->translations->firstWhere('locale', $locale);
            if ($translation && isset($translation->{$field})) {
                return $translation->{$field};
            }
        }

        // Fallback to the relationship if not eager loaded, or if field not found in eager load
        if (method_exists($this, 'translations')) {
            $translation = $this->translations()->where('locale', $locale)->first();
            if ($translation && isset($translation->{$field})) {
                return $translation->{$field};
            }
        }

        // Fallback to the base field value
        return $this->{$field} ?? null;
    }

    /**
     * Get translation for a field (alias for trans)
     */
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $value = $this->trans($field, $locale);

        return is_string($value) ? $value : (string) $value;
    }

    /**
     * Check if field has translation in database
     */
    public function hasTranslation(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return $this->translations->where('locale', $locale)->whereNotNull($field)->isNotEmpty();
        }

        if (method_exists($this, 'translations')) {
            return $this->translations()->where('locale', $locale)->whereNotNull($field)->exists();
        }

        return false;
    }

    /**
     * Get all translations for a field from database
     */
    public function getAllTranslations(string $field): array
    {
        if (! method_exists($this, 'translations')) {
            return [];
        }

        return $this->translations()
            ->whereNotNull($field)
            ->pluck($field, 'locale')
            ->toArray();
    }
}