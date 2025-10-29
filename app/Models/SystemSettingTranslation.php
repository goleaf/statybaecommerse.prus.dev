<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Database\Factories\SystemSettingTranslationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SystemSettingTranslation
 *
 * Eloquent model representing the SystemSettingTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation query()
 *
 * @mixin \Eloquent
 */
final class SystemSettingTranslation extends Model
{
    /** @use HasFactory<SystemSettingTranslationFactory> */
    use HasFactory;

    use OrdersByName;
    use SoftDeletes;

    /**
     * Sort translations by their name value to keep locale selectors intuitive
     * across the administration experience.
     */
    protected string $nameColumn = 'name';

    /**
     * Keep the fillable list intentionally minimal to match the public API requirements
     * exercised by the unit tests while still allowing advanced attributes to be handled
     * via explicit setters when required by the application.
     */
    protected $fillable = [
        'system_setting_id',
        'locale',
        'name',
        'description',
        'help_text',
        'rich_description',
        'attachments',
        'meta',
        'metadata',
        'tags',
        'is_active',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'meta'        => 'array',
        'tags'        => 'array',
        'attachments' => 'array',
        'is_active'   => 'boolean',
        'is_public'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    /**
     * Define the inverse relationship to the owning system setting.
     * This comment clarifies the intent to aid future contributors.
     */
    /**
     * @return BelongsTo<SystemSetting, self>
     */
    public function systemSetting(): BelongsTo
    {
        /** @var BelongsTo<SystemSetting, self> $relation */
        $relation = $this->belongsTo(SystemSetting::class);

        // Ensure the parent record remains accessible even when soft deleted or scoped elsewhere.
        return $relation->withoutGlobalScopes()->withTrashed();
    }

    /**
     * Scope the query to a specific locale while ensuring the provided code
     * is normalised to match the database column expectations.
     */
    /**
     * @param  Builder<SystemSettingTranslation> $query
     * @return Builder<SystemSettingTranslation>
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        // Normalise the locale code because upstream callers may provide uppercase variants.
        $normalisedLocale = mb_strtolower($locale);

        return $query->where('locale', $normalisedLocale);
    }

    /**
     * Scope the query to a particular system setting identifier or model instance.
     */
    /**
     * @param  Builder<SystemSettingTranslation> $query
     * @return Builder<SystemSettingTranslation>
     */
    public function scopeForSystemSetting(Builder $query, SystemSetting|int $systemSetting): Builder
    {
        // Guard against both raw identifiers and hydrated model instances for flexibility.
        $systemSettingId = $systemSetting instanceof SystemSetting ? $systemSetting->getKey() : $systemSetting;

        return $query->where('system_setting_id', $systemSettingId);
    }

    /**
     * Locate a translation for the given system setting and locale, returning null when absent.
     */
    public static function findForLocale(SystemSetting|int $systemSetting, string $locale): ?self
    {
        // Chain the reusable scopes so we keep a single source of truth for query fragments.
        return self::query()
            ->forSystemSetting($systemSetting)
            ->forLocale($locale)
            ->first();
    }

    /**
     * Gather all translations associated with a system setting ordered by locale for deterministic UIs.
     */
    /**
     * @return Collection<int, self>
     */
    public static function allForSystemSetting(SystemSetting|int $systemSetting): Collection
    {
        return self::query()
            ->forSystemSetting($systemSetting)
            ->orderBy('locale')
            ->get();
    }

    /**
     * Convenience wrapper that surfaces how many records exist for a locale across the dataset.
     */
    public static function countForLocale(string $locale): int
    {
        return (int) self::query()
            ->forLocale($locale)
            ->count();
    }
}
