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

        // Monolog guarantees a DateTimeImmutable instance, so reformat it for Kibana dashboards.
        $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\\TH:i:s.v\\Z');
        $extra['@timestamp'] = $timestamp;
        $extra['formatted_datetime'] = $timestamp;

        $configuredServiceName = config('app.name', 'laravel');
        $serviceName = $this->serviceName !== ''
            ? $this->serviceName
            : (is_string($configuredServiceName) ? $configuredServiceName : 'laravel');

        $configuredEnvironment = config('app.env', 'production');
        $environment = $this->environment !== ''
            ? $this->environment
            : (is_string($configuredEnvironment) ? $configuredEnvironment : 'production');

        $extra['service'] = [
            'name'        => $serviceName,
            'environment' => $environment,
        ];

        // Capture the current PHP process identifier so Kibana can group log entries correctly.
        $pid = function_exists('getmypid') ? getmypid() : false;

        if ($pid !== false) {
            $extra['process'] = [
                'pid' => $pid,
            ];
        }

        return $record->with(extra: $extra);
    }
}
