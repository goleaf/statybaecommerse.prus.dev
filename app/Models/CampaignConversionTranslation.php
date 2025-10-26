<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
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
    use OrdersByName; // Allow translated labels to be sorted consistently for reporting.

    /**
     * Explicitly define the table to keep the translation namespace flexible.
     */
    protected $table = 'campaign_conversion_translations';

    /**
     * Guard the attributes that can be mass-assigned for safer factories.
     *
     * @var array<int, string>
     */
    protected $fillable = ['campaign_conversion_id', 'locale', 'conversion_type_label', 'status_label', 'notes', 'custom_data'];

    /**
     * Cast the JSON attribute into an array for convenient access in code.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_data' => 'array',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        // Defer to the property to keep assertions consistent while allowing overrides.
        return $this->casts;
    }

    /**
     * Column utilised by the OrdersByName scope for alphabetic listings.
     */
    protected string $nameColumn = 'conversion_type_label';

    /**
     * Provide the owning conversion for downstream analytics queries.
     */
    public function campaignConversion(): BelongsTo
    {
        return $this->belongsTo(CampaignConversion::class);
    }

    /**
     * Narrow the dataset to a specific locale which improves translation lookups.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
}
