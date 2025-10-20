<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeInterface;
use DateTimeZone;

final class KibanaContextProcessor
{
    public function __construct(
        private readonly string $serviceName = '',
        private readonly string $environment = '',
        private readonly DateTimeZone $timezone = new DateTimeZone('UTC'),
    ) {
    }

    public function __invoke(array $record): array
    {
        if (($record['datetime'] ?? null) instanceof DateTimeInterface) {
            /** @var DateTimeInterface $datetime */
            $datetime = $record['datetime'];
            $timestamp = $datetime->setTimezone($this->timezone)->format('Y-m-d\TH:i:s.v\Z');
            $record['datetime'] = $timestamp;
            $record['extra']['@timestamp'] = $timestamp;
        }

        $serviceName = $this->serviceName !== '' ? $this->serviceName : config('app.name', 'laravel');
        $environment = $this->environment !== '' ? $this->environment : (string) config('app.env', 'production');

        $record['extra']['service'] = [
            'name' => $serviceName,
            'environment' => $environment,
        ];

        $pid = getmypid();
        if ($pid !== false) {
            $record['extra']['process'] = [
                'pid' => $pid,
            ];
        }

        return $record;
    }
}
