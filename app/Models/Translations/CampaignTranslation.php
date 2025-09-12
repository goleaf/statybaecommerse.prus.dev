<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CampaignTranslation extends Model
{
    protected $table = 'campaign_translations';

    protected $fillable = [
        'campaign_id',
        'locale',
        'name',
        'slug',
        'description',
        'subject',
        'content',
        'cta_text',
        'banner_alt_text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
    ];

    public $timestamps = true;

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Campaign::class);
    }
}
