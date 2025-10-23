<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeInterface;
use DateTimeZone;
use Monolog\LogRecord;

final class KibanaContextProcessor
{
    public function __construct(
        private readonly string $serviceName = '',
        private readonly string $environment = '',
        private readonly DateTimeZone $timezone = new DateTimeZone('UTC'),
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        if ($record->datetime instanceof DateTimeInterface) {
            $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\\TH:i:s.v\\Z');
            $extra['@timestamp'] = $timestamp;
            $extra['formatted_datetime'] = $timestamp;
        }

        $serviceName = $this->serviceName !== '' ? $this->serviceName : config('app.name', 'laravel');
        $environment = $this->environment !== '' ? $this->environment : (string) config('app.env', 'production');

        $extra['service'] = [
            'name' => $serviceName,
            'environment' => $environment,
        ];

        $pid = null;

        if (function_exists('posix_getpid')) {
            // Prefer posix_getpid when available because it avoids triggering warnings on systems without pcntl.
            $pid = posix_getpid();
        } elseif (function_exists('getmypid')) {
            $pid = getmypid();
        }

        if (is_int($pid) && $pid > 0) {
            $extra['process'] = [
                'pid' => $pid,
            ];
        }

        return $record->with(extra: $extra);
    }
}
