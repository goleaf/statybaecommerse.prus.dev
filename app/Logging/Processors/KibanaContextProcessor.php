<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeInterface;
use DateTimeZone;
use Monolog\LogRecord;
use function getmypid;

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

        // We fetch the current process identifier so Kibana dashboards can
        // correlate log lines with the PHP worker that emitted them. The
        // native getmypid() helper returns false on unsupported platforms,
        // so we guard the enrichment to avoid emitting undefined values.
        $pid = getmypid();

        if ($pid !== false) {
            $extra['process'] = [
                'pid' => $pid,
            ];
        }

        return $record->with(extra: $extra);
    }
}
