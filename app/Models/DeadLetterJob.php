<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

/**
 * @property int                       $id
 * @property string                    $uuid
 * @property string                    $connection
 * @property string                    $queue
 * @property string                    $job
 * @property int                       $attempts
 * @property string                    $payload
 * @property string|null               $exception
 * @property array<string, mixed>|null $context
 */
final class DeadLetterJob extends Model
{
    /**
     * Explicitly list the attributes that may be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'job',
        'attempts',
        'payload',
        'exception',
        'context',
        'failed_at',
    ];

    /**
     * @var string|null
     */
    protected $table = 'dead_letter_jobs';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context'   => 'array',
        'failed_at' => 'datetime',
    ];

    /**
     * Automatically generate identifiers when creating fresh records.
     */
    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (DeadLetterJob $job): void {
            // Ensure a UUID exists so consumers can safely reference a job instance.
            $job->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Provide a convenient accessor for decoding the raw job payload.
     *
     * @return array<string, mixed>
     */
    public function payloadData(): array
    {
        // Safely decode the JSON payload while always returning an array structure.
        /** @var array<string, mixed> $decoded */
        $decoded = safe_json_decode_array($this->payload ?? '') ?: [];

        return $decoded;
    }

    /**
     * Retrieve a single value from the optional context payload.
     */
    public function contextValue(string $key, mixed $default = null): mixed
    {
        // Delegate to data_get so nested keys like "retry.window" remain supported.
        return data_get($this->context ?? [], $key, $default);
    }

    /**
     * Scope a query to a specific queue connection.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForConnection(Builder $query, string $connection): Builder
    {
        // Filter by the connection column to focus analytics or clean-up efforts.
        return $query->where('connection', $connection);
    }

    /**
     * Scope a query to a specific queue name.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForQueue(Builder $query, string $queue): Builder
    {
        // Narrow the result set to only the queue of interest.
        return $query->where('queue', $queue);
    }

    /**
     * Scope a query by UUID for quick lookups.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForUuid(Builder $query, string $uuid): Builder
    {
        // Use an exact match as UUIDs are unique identifiers.
        return $query->where('uuid', $uuid);
    }

    /**
     * Scope jobs that failed after the provided timestamp.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeFailedSince(Builder $query, CarbonInterface $since): Builder
    {
        // Compare the failure timestamp so we can build recent failure dashboards.
        return $query->where('failed_at', '>=', $since);
    }

    /**
     * Scope jobs that have retried more times than the supplied threshold.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeAttemptedMoreThan(Builder $query, int $attempts): Builder
    {
        // Capture only noisy jobs that continually fail and exceed retry expectations.
        return $query->where('attempts', '>', $attempts);
    }

    /**
     * Replay the job by pushing it back onto the original queue.
     */
    public function requeue(?string $queue = null): void
    {
        Queue::connection($this->connection)->pushRaw(
            $this->payload,
            $queue ?? $this->queue
        );

        $this->delete();
    }
}
