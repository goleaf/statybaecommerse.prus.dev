<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Queue;

use App\Contracts\SystemNotificationSender;
use App\Support\Queue\QueueFailureHandler;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

final class QueueFailureHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();

        config([
            'queue_monitor.dead_letter.enabled' => true,
            'queue_monitor.alerts.enabled' => true,
            'queue_monitor.alerts.window_seconds' => 300,
            'queue_monitor.alerts.failure_threshold' => 2,
        ]);
    }

    public function test_it_ignores_jobs_with_remaining_attempts(): void
    {
        $notificationService = new NotificationRecorder;

        $handler = new QueueFailureHandler(Cache::store(), $notificationService);

        $event = new JobFailed('database', $this->makeJobMock(attempts: 1, maxTries: 3), new RuntimeException('fail'));

        $handler->handle($event);

        $this->assertDatabaseCount('dead_letter_jobs', 0);
        self::assertSame(0, $notificationService->callCount);
    }

    public function test_it_persists_dead_letter_and_sends_alert_on_spike(): void
    {
        $notificationService = new NotificationRecorder;

        $handler = new QueueFailureHandler(Cache::store(), $notificationService);

        $event = new JobFailed('database', $this->makeJobMock(attempts: 3, maxTries: 3), new RuntimeException('Boom'));
        $handler->handle($event);
        $this->assertDatabaseCount('dead_letter_jobs', 1);

        $secondEvent = new JobFailed('database', $this->makeJobMock(attempts: 3, maxTries: 3), new RuntimeException('Boom'));
        $handler->handle($secondEvent);

        $this->assertDatabaseCount('dead_letter_jobs', 2);
        self::assertSame(1, $notificationService->callCount);
        $this->assertSame('Queue failure spike detected', $notificationService->records[0]['title']);
        $this->assertSame('error', $notificationService->records[0]['type']);
    }

    private function makeJobMock(int $attempts, ?int $maxTries): JobContract
    {
        /** @var array<string, mixed> $payload */
        $payload = [
            'displayName' => 'App\\Jobs\\ExampleJob',
            'attempts' => $attempts,
        ];

        return new class($attempts, $maxTries, $payload) implements JobContract
        {
            /**
             * @param  array<string, mixed>  $payload
             */
            public function __construct(
                private readonly int $attempts,
                private readonly ?int $maxTries,
                private readonly array $payload
            ) {}

            public function uuid(): ?string
            {
                $uuid = $this->payload['uuid'] ?? null;

                return \is_string($uuid) ? $uuid : null;
            }

            public function getJobId(): string
            {
                return 'fake-job-id';
            }

            /**
             * @return array<string, mixed>
             */
            public function payload(): array
            {
                return $this->payload;
            }

            public function fire(): void {}

            public function release($delay = 0): void {}

            public function isReleased(): bool
            {
                return false;
            }

            public function delete(): void {}

            public function isDeleted(): bool
            {
                return false;
            }

            public function isDeletedOrReleased(): bool
            {
                return false;
            }

            public function hasFailed(): bool
            {
                return false;
            }

            public function markAsFailed(): void {}

            public function fail($e = null): void {}

            public function attempts(): int
            {
                return $this->attempts;
            }

            public function maxTries(): ?int
            {
                return $this->maxTries;
            }

            public function maxExceptions(): ?int
            {
                return null;
            }

            public function timeout(): ?int
            {
                return null;
            }

            public function retryUntil(): ?int
            {
                return null;
            }

            public function getName(): string
            {
                return 'App\\Jobs\\ExampleJob';
            }

            public function resolveName(): string
            {
                return 'App\\Jobs\\ExampleJob';
            }

            public function resolveQueuedJobClass(): string
            {
                return 'App\\Jobs\\ExampleJob';
            }

            public function getConnectionName(): string
            {
                return 'database';
            }

            public function getQueue(): string
            {
                return 'default';
            }

            public function getRawBody(): string
            {
                return json_encode($this->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        };
    }
}

/**
 * @internal helper used by QueueFailureHandlerTest
 */
final class NotificationRecorder implements SystemNotificationSender
{
    public int $callCount = 0;

    /**
     * @var array<int, array{title: string, message: string, type: string}>
     */
    public array $records = [];

    public function sendSystemNotification(string $title, string $message, string $type = 'info'): void
    {
        $this->callCount++;
        $this->records[] = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ];
    }
}
