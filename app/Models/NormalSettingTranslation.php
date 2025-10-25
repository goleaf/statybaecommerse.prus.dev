<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NormalSettingTranslationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NormalSettingTranslation
 *
 * Eloquent model representing the NormalSettingTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation query()
 *
 * @mixin \Eloquent
 */
final class NormalSettingTranslation extends Model
{
    /** @use HasFactory<NormalSettingTranslationFactory> */
    use HasFactory;

    protected $table = 'enhanced_settings_translations';

    protected $fillable = ['enhanced_setting_id', 'locale', 'description', 'display_name', 'help_text'];

    /**
     * Handle enhancedSetting functionality with proper error handling.
     *
     * @return BelongsTo<NormalSetting, NormalSettingTranslation>
     */
    public function enhancedSetting(): BelongsTo
    {
        /** @var BelongsTo<NormalSetting, NormalSettingTranslation> $relation */
        $relation = $this->belongsTo(NormalSetting::class, 'enhanced_setting_id');

        // Return the hydrated belongsTo relation instance for fluent chaining.
        return $relation;
    }

    /**
     * Scope the query to always order translations by the user-facing display name.
     *
     * @param  Builder<NormalSettingTranslation> $query
     * @return Builder<NormalSettingTranslation>
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Sorting by display_name keeps listings predictable for administrators and storefront consumers.
        return $query->orderBy('display_name');
    }
}
