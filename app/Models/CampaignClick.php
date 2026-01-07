<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\CampaignClickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignClick Model (Deprecated)
 * 
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
final class CampaignClick extends Model
{
    /** @use HasFactory<CampaignClickFactory> */
    use HasFactory;
    use HasTranslations;

    protected $table = 'campaign_clicks';

    protected $fillable = [
        'campaign_id',
        'session_id',
        'ip_address',
        'user_agent',
        'click_type',
        'clicked_url',
        'customer_id',
        'clicked_at',
        'referer',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'conversion_value',
        'is_converted',
        'conversion_data',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'conversion_value' => 'decimal:2',
        'is_converted' => 'boolean',
        'conversion_data' => 'array',
    ];

    public array $translatable = [
        'click_type_label',
        'device_type_label',
        'browser_label',
        'os_label',
        'notes',
        'custom_data',
    ];

    /**
     * Get the campaign that owns the click.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the customer that made the click.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}