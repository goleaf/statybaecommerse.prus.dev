<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['campaign_conversion_id', 'locale', 'notes', 'custom_attributes'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['custom_attributes' => 'array'];
    }
}
