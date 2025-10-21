<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateStockExport;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GenerateStockExport::class)]
final class GenerateStockExportTest extends TestCase
{
    public function test_retry_configuration_is_defined(): void
    {
        $job = new GenerateStockExport(['location_id' => 1], 10);

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 120, 300], $job->backoff());
        $this->assertSame('exports', $job->queue);
    }
}
