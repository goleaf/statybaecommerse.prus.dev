<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeZone;
use Monolog\LogRecord;
use Throwable;

final class KibanaContextProcessor
{
    public function __construct(
        private readonly string $serviceName = 'app',
        private readonly string $fallbackEnvironment = 'production',
        private readonly ?DateTimeZone $timezone = null,
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $environment = $this->resolveEnvironment();
        $timestamp = $record->datetime
            ->setTimezone($this->timezone ?? new DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $extra = $record->extra;
        $extra['service'] = $this->serviceName;
        $extra['environment'] = $environment;
        $extra['timestamp'] = $timestamp;
        $extra['process'] = [
            'pid' => getmypid(),
        ];

        return $record->with(extra: $extra);
    }

    private function resolveEnvironment(): string
    {
        try {
            $resolved = app()->environment();

            return is_string($resolved) && $resolved !== '' ? $resolved : $this->fallbackEnvironment;
        } catch (Throwable) {
            return $this->fallbackEnvironment;
        }
    }
}
