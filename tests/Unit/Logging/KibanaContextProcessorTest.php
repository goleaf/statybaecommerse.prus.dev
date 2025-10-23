<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Logging\Processors\KibanaContextProcessor;
use DateTimeImmutable;
use DateTimeZone;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

final class KibanaContextProcessorTest extends TestCase
{
    public function test_it_adds_process_information_when_available(): void
    {
        // Create a log record that mimics Monolog's runtime payload while providing a fixed timestamp.
        $record = new LogRecord(
            datetime: new DateTimeImmutable('2024-01-01T12:00:00Z'),
            channel: 'test-channel',
            level: Level::Info,
            message: 'testing',
            context: [],
            extra: [],
        );

        // Run the processor with explicit configuration to ensure deterministic service metadata.
        $processor = new KibanaContextProcessor('test-service', 'testing', new DateTimeZone('UTC'));

        $processedRecord = $processor($record);

        // When `getmypid()` is supported we expect the process identifier to be added to the extra payload.
        $this->assertArrayHasKey('process', $processedRecord->extra);
        $this->assertSame(getmypid(), $processedRecord->extra['process']['pid']);
    }
}
