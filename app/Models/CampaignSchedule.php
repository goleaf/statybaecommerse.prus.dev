<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Builder;
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
final class CampaignSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'schedule_type', 'schedule_config', 'next_run_at', 'last_run_at', 'is_active'];

    /**
     * Provide sensible defaults to make working with optional attributes safer.
     *
     * The JSON configuration column defaults to an empty array and schedules
     * are created as active unless explicitly disabled by the caller.
     */
    protected $attributes = [
        'schedule_config' => '[]',
        'is_active'       => true,
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'schedule_config' => 'array',
            'next_run_at'     => 'datetime',
            'last_run_at'     => 'datetime',
            'schedule_type'   => ScheduleType::class,
            'is_active'       => 'boolean',
        ];
    }

    /**
     * Provide a reusable scope for callers that specifically need only active schedules.
     *
     * We avoid applying the active scope globally so Filament can manage inactive
     * schedules in the admin UI without silently excluding them from queries.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Limit the query to schedules that are due for execution.
     *
     * We require the schedule to be active, have a planned run timestamp,
     * and ensure the time is in the past (or now) so workers know which jobs
     * to execute next.
     */
    public function scopeDueForExecution(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());
    }

    /**
     * Allow consumers to filter schedules by the desired schedule type.
     *
     * Accepting both the enum instance and raw string simplifies usage in
     * places where casting has already occurred or where the raw column value
     * is still available (for example, HTTP filters).
     */
    public function scopeForType(Builder $query, ScheduleType|string $type): Builder
    {
        $resolvedType = $type instanceof ScheduleType ? $type : ScheduleType::from((string) $type);

        return $query->where('schedule_type', $resolvedType->value);
    }

    /**
     * Handle campaign functionality with proper error handling.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
