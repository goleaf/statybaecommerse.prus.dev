<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignSchedule
 *
 * Eloquent model representing the CampaignSchedule entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignSchedule query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'schedule_type', 'schedule_config', 'next_run_at', 'last_run_at', 'is_active'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['schedule_config' => 'array', 'next_run_at' => 'datetime', 'last_run_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /**
     * Handle campaign functionality with proper error handling.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
