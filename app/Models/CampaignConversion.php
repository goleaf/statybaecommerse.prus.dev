<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\CampaignConversionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignConversion Model (Deprecated)
 * 
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
final class CampaignConversion extends Model
{
    /** @use HasFactory<CampaignConversionFactory> */
    use HasFactory;
    use HasTranslations;

    protected $table = 'campaign_conversions';

    protected $fillable = [
        'campaign_id',
        'click_id',
        'order_id',
        'customer_id',
        'conversion_type',
        'conversion_value',
        'session_id',
        'conversion_data',
        'converted_at',
        'status',
        'source',
        'medium',
        'campaign_name',
        'utm_content',
        'utm_term',
        'referrer',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'is_verified',
        'is_attributed',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'conversion_data' => 'array',
        'converted_at' => 'datetime',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'is_verified' => 'boolean',
        'is_attributed' => 'boolean',
    ];

    public array $translatable = [
        'conversion_type_label',
        'status_label',
        'notes',
        'custom_data',
        'custom_attributes',
    ];

    /**
     * Get the campaign that owns the conversion.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the click that led to this conversion.
     */
    public function click(): BelongsTo
    {
        return $this->belongsTo(CampaignClick::class, 'click_id');
    }

    /**
     * Get the order associated with this conversion.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer that made the conversion.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}