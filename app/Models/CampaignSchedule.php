<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CampaignScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignSchedule Model (Deprecated)
 * 
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
final class CampaignSchedule extends Model
{
    /** @use HasFactory<CampaignScheduleFactory> */
    use HasFactory;

    protected $table = 'campaign_schedules';

    protected $fillable = [
        'campaign_id',
        'schedule_type',
        'schedule_config',
        'next_run_at',
        'last_run_at',
        'is_active',
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the campaign that owns the schedule.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}