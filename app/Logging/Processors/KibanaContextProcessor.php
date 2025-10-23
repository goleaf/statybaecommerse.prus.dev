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

    public function __invoke(LogRecord|array $record): LogRecord|array
    {
        $serviceName = $this->serviceName !== '' ? $this->serviceName : config('app.name', 'laravel');
        $environment = $this->environment !== '' ? $this->environment : (string) config('app.env', 'production');
        $pid = getmypid();

        if ($record instanceof LogRecord) {
            $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\TH:i:s.v\Z');

            $extra = $record->extra;
            $extra['@timestamp'] = $timestamp;
            $extra['service'] = [
                'name'        => $serviceName,
                'environment' => $environment,
            ];

            if ($pid !== false) {
                $extra['process']['pid'] = $pid;
            }

            return $record->with(extra: $extra);
        }

        if (($record['datetime'] ?? null) instanceof DateTimeInterface) {
            /** @var DateTimeInterface $datetime */
            $datetime = $record['datetime'];
            $timestamp = $datetime->setTimezone($this->timezone)->format('Y-m-d\TH:i:s.v\Z');
            $record['datetime'] = $timestamp;
            $record['extra']['@timestamp'] = $timestamp;
        }

        $record['extra']['service'] = [
            'name'        => $serviceName,
            'environment' => $environment,
        ];

        if ($pid !== false) {
            $extra['process'] = [
                'pid' => $pid,
            ];
        }

        return $record->with(extra: $extra);
    }
}
