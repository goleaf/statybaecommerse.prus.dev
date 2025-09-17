<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
/**
 * CampaignConversionTranslation
 * 
 * Eloquent model representing the CampaignConversionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversionTranslation query()
 * @mixin \Eloquent
 */
final class CampaignConversionTranslation extends Model
{
    protected $table = 'campaign_conversion_translations';
    protected $fillable = ['campaign_conversion_id', 'locale', 'conversion_type_label', 'status_label', 'notes', 'custom_data'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['custom_data' => 'array'];
    }
    public $timestamps = false;
}