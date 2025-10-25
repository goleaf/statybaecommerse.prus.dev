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

    /**
     * Allow translation listings to fall back to alphabetical ordering by notes when available.
     */
    use OrdersByName;

    /**
     * Order translation rows by their notes column because a dedicated title field is not present.
     */
    protected string $nameColumn = 'notes';

    /**
     * Explicitly define the table to keep the translation namespace flexible.
     */
    protected $table = 'campaign_conversion_translations';

    /**
     * Guard the attributes that can be mass-assigned for safer factories.
     *
     * @var array<int, string>
     */
    protected $fillable = ['campaign_conversion_id', 'locale', 'notes', 'custom_attributes'];

    /**
     * Cast the JSON attribute into an array for convenient access in code.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_attributes' => 'array',
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
