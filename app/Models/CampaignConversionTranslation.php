<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignConversionTranslation
 *
 * Eloquent model representing the CampaignConversionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation query()
 *
 * @mixin \Eloquent
 */
final class CampaignConversionTranslation extends Model
{
    use HasFactory;

    protected $table = 'campaign_conversion_translations';

    /** @var list<string> */
    protected $fillable = [
        'campaign_conversion_id',
        'locale',
        'notes',
        'custom_attributes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'custom_attributes' => 'array',
    ];

    public function campaignConversion(): BelongsTo
    {
        return $this->belongsTo(CampaignConversion::class);
    }

    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
}
