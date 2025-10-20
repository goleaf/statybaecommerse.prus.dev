<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Jobs\GenerateReportsJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class GenerateReportsCommandTest extends TestCase
{
    public function test_command_dispatches_job(): void
    {
        Queue::fake();

        $this->artisan('reports:generate --type=sales --format=json --output=storage/reports')
            ->assertExitCode(0);

        Queue::assertPushed(GenerateReportsJob::class, function (GenerateReportsJob $job, string $queue): bool {
            return $queue === 'reports' && $job->backoff() === [60, 120, 300];
        });
    }

    public function test_invalid_type_fails_fast(): void
    {
        Queue::fake();

        $this->artisan('reports:generate --type=unknown')
            ->assertExitCode(1);

        Queue::assertNothingPushed();
    }

    public function test_invalid_format_fails_fast(): void
    {
        Queue::fake();

        $this->artisan('reports:generate --format=xml')
            ->assertExitCode(1);

        Queue::assertNothingPushed();
    }
}
