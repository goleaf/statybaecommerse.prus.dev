<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final /**
 * CampaignClickTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CampaignClickTranslation extends Model
{
    protected $table = 'campaign_click_translations';

    protected $fillable = [
        'campaign_click_id',
        'locale',
        'click_type_label',
        'device_type_label',
        'browser_label',
        'os_label',
        'notes',
        'custom_data',
    ];

    protected function casts(): array
    {
        return [
            'custom_data' => 'array',
        ];
    }

    public $timestamps = false;
}
