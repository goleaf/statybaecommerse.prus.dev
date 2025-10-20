<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateReportsJob;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GenerateReportsJob::class)]
final class GenerateReportsJobTest extends TestCase
{
    public function test_retry_configuration_is_defined(): void
    {
        $job = new GenerateReportsJob('sales', 'storage/reports', 'json', []);

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 120, 300], $job->backoff());
        $this->assertSame('reports', $job->queue);
        $this->assertContains('type:sales', $job->tags());
    }
}
