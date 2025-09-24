<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

/**
 * CampaignClickTranslation
 *
 * Eloquent model representing the CampaignClickTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClickTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClickTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClickTranslation query()
 *
 * @mixin \Eloquent
 */
final class CampaignClickTranslation extends Model
{
    protected $table = 'campaign_click_translations';

    protected $fillable = ['campaign_click_id', 'locale', 'click_type_label', 'device_type_label', 'browser_label', 'os_label', 'notes', 'custom_data'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['custom_data' => 'array'];
    }

    public $timestamps = false;
}
