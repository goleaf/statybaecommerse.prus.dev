<?php

declare(strict_types=1);

namespace App\Support\Queue;

use App\Contracts\SystemNotificationSender;
use App\Models\DeadLetterJob;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_string;

use Throwable;

final class QueueFailureHandler
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly SystemNotificationSender $notificationService,
    ) {}

    public function handle(JobFailed $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $deadLetter = $this->storeDeadLetter($event);

        $this->maybeAlertOnSpike($event, $deadLetter);
    }

    private function shouldHandle(JobFailed $event): bool
    {
        $maxTries = $event->job->maxTries();

        if ($maxTries === null) {
            return true;
        }

        return $event->job->attempts() >= $maxTries;
    }

    private function storeDeadLetter(JobFailed $event): ?DeadLetterJob
    {
        if (! config('queue_monitor.dead_letter.enabled', true)) {
            return null;
        }

        try {
            $payload = $this->normalizePayload($event);

            $deadLetter = DeadLetterJob::create([
                'uuid'       => Str::uuid()->toString(),
                'connection' => $event->connectionName,
                'queue'      => $event->job->getQueue(),
                'job'        => $event->job->resolveName(),
                'attempts'   => $event->job->attempts(),
                'payload'    => $payload,
                'exception'  => $event->exception->getMessage(),
                'context'    => [
                    'exception_class' => $event->exception::class,
                    'max_tries'       => $event->job->maxTries(),
                ],
                'failed_at' => now(),
            ]);

            Log::warning('Job moved to dead-letter queue', [
                'dead_letter_id' => $deadLetter->id,
                'uuid'           => $deadLetter->uuid,
                'job'            => $deadLetter->job,
                'queue'          => $deadLetter->queue,
                'connection'     => $deadLetter->connection,
                'attempts'       => $deadLetter->attempts,
            ]);

            return $deadLetter;
        } catch (Throwable $exception) {
            Log::error('Failed to persist job in dead-letter queue', [
                'job'   => $event->job->resolveName(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizePayload(JobFailed $event): string
    {
        $payload = $event->job->payload();
        $payload['attempts'] = 0;

        try {
            return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable) {
            $encoded = json_encode($payload);

            return is_string($encoded)
                ? $encoded
                : (string) $event->job->getRawBody();
        }
    }

    private function maybeAlertOnSpike(JobFailed $event, ?DeadLetterJob $deadLetter): void
    {
        $config = config('queue_monitor.alerts');

        if (! is_array($config) || ! ($config['enabled'] ?? false)) {
            return;
        }

        $window = $this->sanitizePositiveInt($config['window_seconds'] ?? 300, 60);
        $threshold = $this->sanitizePositiveInt($config['failure_threshold'] ?? 5, 1);
        $timestamp = (int) now()->timestamp;
        $bucket = intdiv($timestamp, $window);
        $key = 'queue:failure:spike:' . $bucket;

        /** @var array{count:mixed, alerted:mixed}|null $state */
        $state = $this->cache->get($key);

        if (! is_array($state)) {
            $state = ['count' => 0, 'alerted' => false];
        }

        $count = $this->sanitizeInteger($state['count'] ?? 0, 0);
        $alerted = $this->sanitizeBoolean($state['alerted'] ?? false);

        $state = [
            'count'   => $count,
            'alerted' => $alerted,
        ];

        $state['count']++;

        $shouldAlert = $state['alerted'] === false && $state['count'] >= $threshold;

        if ($shouldAlert) {
            $state['alerted'] = true;
            $this->sendAlert($event, $state['count'], $window, $deadLetter);
        }

        $this->cache->put($key, $state, now()->addSeconds($window));
    }

    private function sendAlert(JobFailed $event, int $count, int $window, ?DeadLetterJob $deadLetter): void
    {
        $jobName = $event->job->resolveName();
        $queue = $event->job->getQueue();
        $connection = $event->connectionName;

        $message = sprintf(
            '%d queue job failures detected within %d seconds. Latest failure: %s on %s:%s (%s).',
            $count,
            $window,
            $jobName,
            $connection,
            $queue,
            $event->exception::class
        );

        if ($deadLetter !== null) {
            $message .= sprintf(' Dead-letter reference: #%d (%s).', $deadLetter->id, $deadLetter->uuid);
        }

        try {
            $this->notificationService->sendSystemNotification(
                'Queue failure spike detected',
                $message,
                'error'
            );
        } catch (Throwable $exception) {
            Log::error('Failed to send queue spike notification', [
                'job'   => $jobName,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sanitizePositiveInt(mixed $value, int $minimum): int
    {
        if (is_int($value)) {
            return max($value, $minimum);
        }

        if (is_numeric($value)) {
            $normalized = (int) $value;

            if ($normalized >= $minimum) {
                return $normalized;
            }
        }

        return $minimum;
    }

    private function sanitizeInteger(mixed $value, int $fallback): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $fallback;
    }

    private function sanitizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return (bool) $value;
    }
}
