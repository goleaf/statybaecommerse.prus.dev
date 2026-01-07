<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CampaignViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignView Model (Deprecated)
 * 
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
final class CampaignView extends Model
{
    /** @use HasFactory<CampaignViewFactory> */
    use HasFactory;

    protected $table = 'campaign_views';

    protected $fillable = [
        'campaign_id',
        'session_id',
        'ip_address',
        'user_agent',
        'referer',
        'customer_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the campaign that owns the view.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the customer that made the view.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}