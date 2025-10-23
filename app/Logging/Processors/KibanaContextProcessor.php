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
        if ($record->datetime instanceof DateTimeInterface) {
            // Provide an ISO8601 timestamp for Kibana-compatible ingestion.
            $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\\TH:i:s.v\\Z');
            $record->extra['@timestamp'] = $timestamp;
            $record->extra['formatted_datetime'] = $timestamp;
        }

        // Surface the service metadata so dashboards can differentiate between
        // multiple Laravel workers feeding into the same Logstash pipeline.
        $serviceName = $this->serviceName !== '' ? $this->serviceName : config('app.name', 'laravel');
        $environment = $this->environment !== '' ? $this->environment : (string) config('app.env', 'production');

        $record->extra['service'] = [
            'name'        => $serviceName,
            'environment' => $environment,
        ];

        // Expose the environment separately so search filters in Kibana can
        // query logs by the current Laravel environment quickly.
        $record->extra['environment'] = app()->environment();

        // Capture the current PHP process identifier so log aggregators can
        // group events reliably, while guarding against hosting environments
        // that disable getmypid().
        if (function_exists('getmypid')) {
            $pid = getmypid();

            if (is_int($pid) && $pid > 0) {
                $processContext = $record->extra['process'] ?? [];
                if (! is_array($processContext)) {
                    $processContext = [];
                }

                $processContext['pid'] = $pid;
                $record->extra['process'] = $processContext;
            }
        }

        return $record;
    }
}
