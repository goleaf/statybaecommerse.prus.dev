<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UiTranslation
 *
 * Eloquent model for storing UI interface translations in the database.
 * Provides key-value translation storage for admin interface strings.
 *
 * @property int $id
 * @property string $key
 * @property string $locale
 * @property string $value
 * @property string|null $group
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UiTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UiTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UiTranslation query()
 *
 * @mixin \Eloquent
 */
final class UiTranslation extends Model
{
    use HasFactory;

    protected $table = 'ui_translations';

    protected $fillable = [
        'key',
        'locale',
        'value',
        'group',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Scope to filter by translation group
     */
    public function scopeForGroup($query, string $group): void
    {
        $query->where('group', $group);
    }

    /**
     * Scope to filter by locale
     */
    public function scopeForLocale($query, string $locale): void
    {
        $query->where('locale', $locale);
    }

    /**
     * Scope to filter by key pattern
     */
    public function scopeForKeyPattern($query, string $pattern): void
    {
        $query->where('key', 'like', $pattern);
    }

    /**
     * Get translation value with fallback
     */
    public static function getTranslation(string $key, string $locale = 'lt', ?string $fallbackLocale = 'en'): ?string
    {
        $translation = self::where('key', $key)
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            return $translation->value;
        }

        if ($fallbackLocale && $fallbackLocale !== $locale) {
            $fallback = self::where('key', $key)
                ->where('locale', $fallbackLocale)
                ->first();

            return $fallback?->value;
        }

        return null;
    }

    /**
     * Set or update translation
     */
    public static function setTranslation(string $key, string $locale, string $value, ?string $group = null): self
    {
        return self::updateOrCreate(
            ['key' => $key, 'locale' => $locale],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get all translations for a group and locale
     */
    public static function getGroupTranslations(string $group, string $locale = 'lt'): array
    {
        return self::forGroup($group)
            ->forLocale($locale)
            ->pluck('value', 'key')
            ->toArray();
    }
}
