<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

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
        $t = $this->translations->firstWhere('locale', $locale);
        if ($t && isset($t->{$field})) {
            $value = $t->{$field};
            if (is_array($value)) {
                return $value[$locale] ?? reset($value) ?? ($this->{$field} ?? null);
            }
            return $value;
        }
        return $this->{$field} ?? null;
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


