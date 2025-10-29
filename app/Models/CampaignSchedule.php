<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use InvalidArgumentException;

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
    /** @use HasFactory<\Database\Factories\CampaignScheduleFactory> */
    use HasFactory;

    public const FILLABLE = [
        'campaign_id',
        'schedule_type',
        'schedule_config',
        'next_run_at',
        'last_run_at',
        'is_active',
    ];

    protected $table = 'campaign_schedules';

    /**
     * Define the attributes that are mass assignable so factories and user
     * input can safely persist schedules.
     *
     * @var list<string>
     */
    protected $fillable = self::FILLABLE;

    /**
     * Cast complex attributes so consumers always work with rich types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'schedule_config' => 'array',
        'next_run_at'     => 'datetime',
        'last_run_at'     => 'datetime',
        'schedule_type'   => ScheduleType::class,
        'is_active'       => 'boolean',
    ];

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
     * Provide a reusable scope for callers that specifically need only active schedules.
     *
     * We avoid applying the active scope globally so Filament can manage inactive
     * schedules in the admin UI without silently excluding them from queries.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
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
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
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
    /**
     * @param  Builder<self>                                       $query
     * @param  ScheduleType|string|array<int, ScheduleType|string> $type
     * @return Builder<self>
     */
    public function scopeForType(Builder $query, ScheduleType|string|array $type): Builder
    {
        // Normalise the input so callers can pass a single value or an array of
        // values (enums or raw strings) without worrying about the underlying
        // storage representation. Wrapping in an array first avoids duplicating
        // the resolution logic across separate code paths.
        $requestedTypes = Arr::wrap($type);
        $validValues = collect(ScheduleType::cases())
            ->map(static fn (ScheduleType $case): string => $case->value)
            ->implode(', ');

        $resolvedTypes = collect($requestedTypes)
            ->map(static function (mixed $value) use ($validValues): ScheduleType {
                if ($value instanceof ScheduleType) {
                    // Developers using enums can pass the backed enum directly;
                    // we return immediately to avoid unnecessary string work.
                    return $value;
                }

                if (! is_string($value)) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid schedule type "%s". Expected one of: %s.',
                        is_scalar($value) ? (string) $value : gettype($value),
                        $validValues,
                    ));
                }

                $normalised = ScheduleType::tryFrom(strtolower(trim($value)));

                if ($normalised === null) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid schedule type "%s". Expected one of: %s.',
                        $value,
                        $validValues,
                    ));
                }

                return $normalised;
            })
            // Using values ensures the query compares against the stored string
            // column values regardless of the input format supplied.
            ->map(static fn (ScheduleType $resolved): string => $resolved->value)
            ->unique()
            ->values()
            ->all();

        if ($resolvedTypes === []) {
            throw new InvalidArgumentException(sprintf(
                'Invalid schedule type input provided. Expected one of: %s.',
                $validValues,
            ));
        }

        return $query->whereIn('schedule_type', $resolvedTypes);
    }

    /**
     * Handle campaign functionality with proper error handling.
     *
     * @return BelongsTo<Campaign, CampaignSchedule>
     *
     * @phpstan-return BelongsTo<Campaign, CampaignSchedule>
     */
    public function campaign(): BelongsTo
    {
        // @phpstan-ignore-next-line The base belongsTo helper already constrains the relation to CampaignSchedule.
        return $this->belongsTo(Campaign::class);
    }
}
