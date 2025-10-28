<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Logging\Processors\KibanaContextProcessor;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
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
        $this->assertIsArray($processedRecord->extra['process']);
        $this->assertSame(getmypid(), $processedRecord->extra['process']['pid']);
    }

    public function test_it_falls_back_to_configured_environment_when_container_is_unavailable(): void
    {
        // Preserve the current container instance so the global `app()` helper can be restored after the test.
        $originalContainer = Container::getInstance();

        $throwingContainer = new class extends Container
        {
            public function environment(): string
            {
                // Mimic the behaviour of a Laravel application that has not been fully bootstrapped yet.
                throw new BindingResolutionException('environment binding has not been registered');
            }
        };

        Container::setInstance($throwingContainer);

        try {
            $record = new LogRecord(
                datetime: new DateTimeImmutable('2024-01-01T12:00:00Z'),
                channel: 'test-channel',
                level: Level::Info,
                message: 'testing',
                context: [],
                extra: [],
            );

            $processor = new KibanaContextProcessor('test-service', 'fallback-env', new DateTimeZone('UTC'));

            $processedRecord = $processor($record);

            // Ensure the fallback environment is still present when the container cannot resolve the binding.
            $this->assertSame('fallback-env', $processedRecord->extra['environment']);
        } finally {
            // Restore the original container to keep subsequent tests isolated.
            Container::setInstance($originalContainer);
        }
    }
}
