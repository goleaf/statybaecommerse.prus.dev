<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CampaignConversionTranslation extends Model
{
    protected $table = 'campaign_conversion_translations';

    protected $fillable = [
        'campaign_conversion_id',
        'locale',
        'conversion_type_label',
        'status_label',
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
