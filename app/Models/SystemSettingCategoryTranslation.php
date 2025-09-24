<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSettingCategoryTranslation
 *
 * Eloquent model representing the SystemSettingCategoryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int $id
 * @property int $system_setting_category_id
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SystemSettingCategory $systemSettingCategory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation forLocale(string $locale)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation forCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation lithuanian()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation english()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation german()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation french()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation spanish()
 *
 * @mixin \Eloquent
 */
final class SystemSettingCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_category_id',
        'locale',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'system_setting_category_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the system setting category that owns the translation.
     */
    public function systemSettingCategory(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class);
    }

    /**
     * Scope translations for a specific locale.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope translations for a specific category.
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('system_setting_category_id', $categoryId);
    }

    /**
     * Scope Lithuanian translations.
     */
    public function scopeLithuanian(Builder $query): Builder
    {
        return $query->where('locale', 'lt');
    }

    /**
     * Scope English translations.
     */
    public function scopeEnglish(Builder $query): Builder
    {
        return $query->where('locale', 'en');
    }

    /**
     * Scope German translations.
     */
    public function scopeGerman(Builder $query): Builder
    {
        return $query->where('locale', 'de');
    }

    /**
     * Scope French translations.
     */
    public function scopeFrench(Builder $query): Builder
    {
        return $query->where('locale', 'fr');
    }

    /**
     * Scope Spanish translations.
     */
    public function scopeSpanish(Builder $query): Builder
    {
        return $query->where('locale', 'es');
    }

    /**
     * Get all available locales.
     */
    public static function getAvailableLocales(): array
    {
        return [
            'lt' => 'Lietuvių',
            'en' => 'English',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
        ];
    }

    /**
     * Get locale display name.
     */
    public function getLocaleDisplayAttribute(): string
    {
        $locales = self::getAvailableLocales();

        return $locales[$this->locale] ?? $this->locale;
    }

    /**
     * Get locale badge color.
     */
    public function getLocaleBadgeColorAttribute(): string
    {
        return match ($this->locale) {
            'en' => 'success',
            'lt' => 'info',
            'de' => 'warning',
            'fr' => 'danger',
            'es' => 'primary',
            default => 'gray',
        };
    }

    /**
     * Check if translation is complete (has name and description).
     */
    public function isComplete(): bool
    {
        return ! empty($this->name) && ! empty($this->description);
    }

    /**
     * Get translation completeness percentage.
     */
    public function getCompletenessAttribute(): int
    {
        $fields = 2; // name and description
        $completed = 0;

        if (! empty($this->name)) {
            $completed++;
        }

        if (! empty($this->description)) {
            $completed++;
        }

        return (int) round(($completed / $fields) * 100);
    }

    /**
     * Get truncated name for display.
     */
    public function getTruncatedNameAttribute(): string
    {
        return strlen($this->name) > 50 ? substr($this->name, 0, 47).'...' : $this->name;
    }

    /**
     * Get truncated description for display.
     */
    public function getTruncatedDescriptionAttribute(): ?string
    {
        if (empty($this->description)) {
            return null;
        }

        return strlen($this->description) > 50 ? substr($this->description, 0, 47).'...' : $this->description;
    }

    /**
     * Get translation statistics for a category.
     */
    public static function getCategoryTranslationStats(int $categoryId): array
    {
        $translations = self::forCategory($categoryId)->get();
        $totalLocales = count(self::getAvailableLocales());

        $stats = [
            'total_locales' => $totalLocales,
            'translated_locales' => $translations->count(),
            'completion_percentage' => (int) round(($translations->count() / $totalLocales) * 100),
            'missing_locales' => [],
            'complete_translations' => $translations->filter(fn ($t) => $t->isComplete())->count(),
            'incomplete_translations' => $translations->filter(fn ($t) => ! $t->isComplete())->count(),
        ];

        $existingLocales = $translations->pluck('locale')->toArray();
        $stats['missing_locales'] = array_diff(array_keys(self::getAvailableLocales()), $existingLocales);

        return $stats;
    }

    /**
     * Get all translations for a category grouped by locale.
     */
    public static function getCategoryTranslationsGrouped(int $categoryId): Collection
    {
        return self::forCategory($categoryId)
            ->with('systemSettingCategory')
            ->orderBy('locale')
            ->get()
            ->groupBy('locale');
    }

    /**
     * Find translation by category and locale.
     */
    public static function findByCategoryAndLocale(int $categoryId, string $locale): ?self
    {
        return self::forCategory($categoryId)
            ->forLocale($locale)
            ->first();
    }

    /**
     * Create or update translation for category and locale.
     */
    public static function updateOrCreateForCategory(int $categoryId, string $locale, array $data): self
    {
        return self::updateOrCreate(
            [
                'system_setting_category_id' => $categoryId,
                'locale' => $locale,
            ],
            array_merge($data, [
                'system_setting_category_id' => $categoryId,
                'locale' => $locale,
            ])
        );
    }

    /**
     * Get translation with fallback to default locale.
     */
    public static function getWithFallback(int $categoryId, string $locale, string $fallbackLocale = 'en'): ?self
    {
        $translation = self::findByCategoryAndLocale($categoryId, $locale);

        if (! $translation && $locale !== $fallbackLocale) {
            $translation = self::findByCategoryAndLocale($categoryId, $fallbackLocale);
        }

        return $translation;
    }

    /**
     * Duplicate translation for another locale.
     */
    public function duplicateForLocale(string $newLocale): self
    {
        return self::create([
            'system_setting_category_id' => $this->system_setting_category_id,
            'locale' => $newLocale,
            'name' => $this->name,
            'description' => $this->description,
        ]);
    }

    /**
     * Get translation quality score based on content length and completeness.
     */
    public function getQualityScoreAttribute(): int
    {
        $score = 0;

        // Name quality (40 points max)
        if (! empty($this->name)) {
            $score += 20; // Base points for having a name
            if (strlen($this->name) >= 5) {
                $score += 20; // Bonus for meaningful name length
            }
        }

        // Description quality (60 points max)
        if (! empty($this->description)) {
            $score += 30; // Base points for having a description
            if (strlen($this->description) >= 20) {
                $score += 30; // Bonus for meaningful description length
            }
        }

        return min(100, $score);
    }

    /**
     * Get quality badge color based on score.
     */
    public function getQualityBadgeColorAttribute(): string
    {
        return match (true) {
            $this->quality_score >= 90 => 'success',
            $this->quality_score >= 70 => 'warning',
            $this->quality_score >= 50 => 'info',
            default => 'danger',
        };
    }
}
