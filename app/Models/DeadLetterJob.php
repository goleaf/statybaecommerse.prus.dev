<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

/**
 * @property int $id
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property string $job
 * @property int $attempts
 * @property string $payload
 * @property string|null $exception
 * @property array<string, mixed>|null $context
 */
final class DeadLetterJob extends Model
{
    /**
     * @var string|null
     */
    protected $table = 'dead_letter_jobs';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'failed_at' => 'datetime',
    ];

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
