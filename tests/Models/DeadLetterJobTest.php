<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\DeadLetterJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DeadLetterJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_uuid_and_casts_attributes(): void
    {
        // Arrange: prepare a failure timestamp so we can assert casting behaviour.
        $failedAt = Carbon::now()->subMinute();

        // Act: create a record without explicitly providing a UUID.
        $job = DeadLetterJob::create([
            'connection' => 'redis',
            'queue'      => 'emails',
            'job'        => 'App\\Jobs\\SendEmail',
            'attempts'   => 2,
            'payload'    => json_encode(['displayName' => 'SendEmailJob'], JSON_THROW_ON_ERROR),
            'context'    => ['recipient' => 'user@example.com'],
            'failed_at'  => $failedAt,
        ]);

        // Assert: the UUID is auto-generated and casts hydrate as expected.
        $this->assertNotEmpty($job->uuid);
        $this->assertSame(36, strlen((string) $job->uuid));
        $this->assertSame('redis', $job->connection);
        $this->assertEquals(['recipient' => 'user@example.com'], $job->context);
        $this->assertSame($failedAt->toDateTimeString(), $job->failed_at->toDateTimeString());
    }

    public function test_payload_helpers_return_expected_values(): void
    {
        // Arrange: persist a job with a structured payload and nested context data.
        $job = DeadLetterJob::create([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'database',
            'queue'      => 'imports',
            'job'        => 'App\\Jobs\\RunImport',
            'attempts'   => 1,
            'payload'    => json_encode(['retry' => ['count' => 3]], JSON_THROW_ON_ERROR),
            'context'    => ['retry' => ['window' => 15]],
            'failed_at'  => Carbon::now(),
        ]);

        // Assert: helper methods surface decoded payload and context lookups.
        $this->assertSame(['retry' => ['count' => 3]], $job->payloadData());
        $this->assertSame(15, $job->contextValue('retry.window'));
        $this->assertSame(99, $job->contextValue('retry.limit', 99));
    }

    public function test_query_scopes_filter_expected_records(): void
    {
        // Arrange: create a recent, noisy job alongside older control data.
        $recent = DeadLetterJob::create([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'redis',
            'queue'      => 'emails',
            'job'        => 'App\\Jobs\\SendEmail',
            'attempts'   => 4,
            'payload'    => '{}',
            'failed_at'  => Carbon::now()->subMinutes(5),
        ]);

        DeadLetterJob::create([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'database',
            'queue'      => 'imports',
            'job'        => 'App\\Jobs\\RunImport',
            'attempts'   => 1,
            'payload'    => '{}',
            'failed_at'  => Carbon::now()->subHours(2),
        ]);

        // Act: chain the custom scopes to isolate the recent redis email job.
        $filtered = DeadLetterJob::query()
            ->forConnection('redis')
            ->forQueue('emails')
            ->failedSince(Carbon::now()->subHour())
            ->attemptedMoreThan(2)
            ->forUuid($recent->uuid)
            ->first();

        // Assert: only the recent record satisfies the scope chain.
        $this->assertNotNull($filtered);
        $this->assertTrue($recent->is($filtered));
    }

    public function test_requeue_pushes_job_back_to_original_queue(): void
    {
        // Arrange: persist a dead letter job and mock the queue manager.
        $payload = json_encode(['displayName' => 'SendNewsletter'], JSON_THROW_ON_ERROR);
        $job = DeadLetterJob::create([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'redis',
            'queue'      => 'emails',
            'job'        => 'App\\Jobs\\SendNewsletter',
            'attempts'   => 3,
            'payload'    => $payload,
            'failed_at'  => Carbon::now(),
        ]);

        // Ensure the queue facade receives the expected replay instruction.
        $original = app('queue');
        $fakeQueue = new class
        {
            /**
             * @var list<array{0: string, 1: ?string}>
             */
            public array $pushes = [];

            public function connection(string $name): self
            {
                // Ignore the requested connection name while preserving the fluent API expectations.
                return $this;
            }

            public function pushRaw(string $payload, ?string $queue = null): void
            {
                // Capture the raw push call so the test can validate arguments.
                $this->pushes[] = [$payload, $queue];
            }
        };

        Queue::swap($fakeQueue);

        // Act: requeue the job and capture the deletion side-effect.
        try {
            $job->requeue();
        } finally {
            // Always restore the original queue manager to keep subsequent tests isolated.
            Queue::swap($original);
        }

        // Assert: the job is removed from the dead letter table after replaying.
        $this->assertDatabaseMissing('dead_letter_jobs', ['id' => $job->id]);
        $this->assertSame([[$payload, 'emails']], $fakeQueue->pushes);
    }
}
