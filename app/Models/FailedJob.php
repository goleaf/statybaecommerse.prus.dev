<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FailedJobFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Lightweight model representing the `failed_jobs` queue table.
 *
 * @property string                          $uuid
 * @property string|null                     $connection
 * @property string|null                     $queue
 * @property string                          $payload
 * @property string                          $exception
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property-read string $job_name
 */
final class FailedJob extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $table = 'failed_jobs';

    protected $guarded = [];

    /**
     * Define the attribute casting rules for the model.
     */
    protected $casts = [
        'failed_at' => 'datetime',
    ];

    protected $appends = ['job_name'];

    /**
     * Get a new factory instance for the model.
     *
     * @param int<0, max>|null     $count
     * @param array<string, mixed> $state
     */
    public static function factory(?int $count = null, array $state = []): FailedJobFactory
    {
        // Mirror Laravel's HasFactory conveniences while keeping the contract simple and type-safe.
        $factory = FailedJobFactory::new();

        if ($count !== null) {
            // Allow callers to request multiple models at once just like the framework helper.
            $factory = $factory->count($count);
        }

        if ($state !== []) {
            // Apply any custom state mutations provided by the caller.
            $factory = $factory->state($state);
        }

        return $factory;
    }

    /**
     * Scope the query to show the most recent failures first for quick triage.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrderedByLatest(Builder $query): Builder
    {
        // Always order by the failure timestamp so the most recent issues bubble up.
        return $query->orderByDesc('failed_at');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FailedJobFactory
    {
        // Hand back the dedicated factory to keep tests expressive and type-safe.
        return FailedJobFactory::new();
    }

    /**
     * Derive a human readable job name from the encoded payload.
     */
    public function getJobNameAttribute(): string
    {
        // We defensively decode the payload to avoid breaking when the data is malformed.
        $payload = safe_json_decode_array($this->payload ?? '');
        $displayName = $payload['displayName'] ?? null;

        if (is_string($displayName) && $displayName !== '') {
            // Return the clean display name whenever the payload includes it.
            return $displayName;
        }

        // Fall back to a sensible label so the UI never renders an empty string.
        return 'unknown';
    }
}
