<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeZone;
use Monolog\LogRecord;

final readonly class KibanaContextProcessor
{
    public function __construct(
        private string $serviceName = '',
        private string $environment = '',
        private DateTimeZone $timezone = new DateTimeZone('UTC'),
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\\TH:i:s.v\\Z');
        $extra['@timestamp'] = $timestamp;
        $extra['formatted_datetime'] = $timestamp;

        // Normalise the configured application name so logs always include a string identifier.
        $configuredName = config('app.name');
        $serviceName = $this->serviceName !== ''
            ? $this->serviceName
            : (is_string($configuredName) && $configuredName !== '' ? $configuredName : 'laravel');

        // Resolve the current environment with a string fallback for unexpected configuration types.
        $configuredEnvironment = config('app.env');
        $environment = $this->environment !== ''
            ? $this->environment
            : (is_string($configuredEnvironment) && $configuredEnvironment !== '' ? $configuredEnvironment : 'production');

        $extra['service'] = [
            'name'        => $serviceName,
            'environment' => $environment,
        ];

        // Capture the current process identifier so downstream log aggregators can
        // correlate individual entries with the PHP worker that emitted them.
        $pid = getmypid();

        if ($pid !== false) {
            $extra['process'] = [
                'pid' => $pid,
            ];
        }

        return $record->with(extra: $extra);
    }
}
