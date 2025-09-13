<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CampaignConversionTranslation extends Model
{
    protected $fillable = [
        'campaign_conversion_id',
        'locale',
        'notes',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'custom_attributes' => 'array',
        ];
    }
}
