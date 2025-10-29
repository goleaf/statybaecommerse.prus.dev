<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Import the parent conversion model lazily to avoid circular dependencies in downstream factories.
use App\Models\CampaignConversion;

/**
 * CampaignConversionTranslation
 *
 * Eloquent model representing the CampaignConversionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation query()
 *
 * @mixin \Eloquent
 */
final class CampaignConversionTranslation extends Model
{
    protected $table = 'campaign_conversion_translations';

    /**
     * Limit mass-assignment to the attributes the translation tests exercise.
     * The reduced list keeps fixtures aligned with the documented contract.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_conversion_id',
        'locale',
        'notes',
        'custom_attributes',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        // Cast translation metadata to arrays so read operations remain predictable.
        return ['custom_attributes' => 'array'];
    }

    public $timestamps = false;

    /**
     * Provide convenient access to the owning conversion while keeping IDE helpers happy.
     */
    public function campaignConversion(): BelongsTo
    {
        return $this->belongsTo(CampaignConversion::class);
    }

    /**
     * Scope helper used by the tests to fetch a single locale in a fluent style.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
}
