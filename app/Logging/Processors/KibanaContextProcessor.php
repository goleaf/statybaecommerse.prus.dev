<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use DateTimeZone;
use Illuminate\Contracts\Container\BindingResolutionException;
use Monolog\LogRecord;
use Throwable;

final readonly class KibanaContextProcessor
{
    public function __construct(
        private string $serviceName = '',
        private string $environment = '',
        private DateTimeZone $timezone = new DateTimeZone('UTC'),
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        // Provide an ISO8601 timestamp for Kibana-compatible ingestion.
        $timestamp = $record->datetime->setTimezone($this->timezone)->format('Y-m-d\\TH:i:s.v\\Z');
        $record->extra['@timestamp'] = $timestamp;
        $record->extra['formatted_datetime'] = $timestamp;

        // Surface the service metadata so dashboards can differentiate between
        // multiple Laravel workers feeding into the same Logstash pipeline.
        $serviceConfig = $this->getConfigValue('app.name', 'laravel');
        $serviceName = $this->serviceName !== ''
            ? $this->serviceName
            : (is_string($serviceConfig) ? $serviceConfig : 'laravel');

        $environmentConfig = $this->getConfigValue('app.env', 'production');
        $environment = $this->environment !== ''
            ? $this->environment
            : (is_string($environmentConfig) ? $environmentConfig : 'production');

        $record->extra['service'] = [
            'name'        => $serviceName,
            'environment' => $environment,
        ];

        // Expose the environment separately so search filters in Kibana can
        // query logs by the current Laravel environment quickly. Default to the
        // configured value when the global helper is not backed by a full
        // Laravel application instance (e.g. during isolated unit tests).
        $appEnvironment = $environment;

        if (function_exists('app')) {
            /** @var mixed $app */
            $app = app();

            if (is_object($app) && method_exists($app, 'environment')) {
                $resolvedEnvironment = null;

                try {
                    $resolvedEnvironment = $app->environment();
                } catch (BindingResolutionException) {
                    // When the container has not been fully bootstrapped the environment
                    // helper attempts to resolve bindings that do not exist yet. In those
                    // scenarios we gracefully fall back to the configured environment.
                }

                if (is_string($resolvedEnvironment) && $resolvedEnvironment !== '') {
                    // Honour the resolved environment when the Laravel application is
                    // bootstrapped while still allowing unit tests or CLI entry points
                    // to rely on the constructor provided fallback string.
                    $appEnvironment = $resolvedEnvironment;
                }
            }
        }

        $record->extra['environment'] = $appEnvironment;

        // Capture the current PHP process identifier so log aggregators can
        // group events reliably, while guarding against hosting environments
        // that disable getmypid().
        $pid = function_exists('getmypid') ? getmypid() : false;

        if ((! is_int($pid) || $pid <= 0) && function_exists('posix_getpid')) {
            $pid = posix_getpid();
        }

        if (is_int($pid)) {
            $processContext = $record->extra['process'] ?? [];

            if (! is_array($processContext)) {
                $processContext = [];
            }

            $processContext['pid'] = $pid;
            $record->extra['process'] = $processContext;
        }

        return $record;
    }

    /**
     * Safely resolve configuration values without assuming the Laravel container has been booted.
     */
    private function getConfigValue(string $key, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            // During isolated unit tests or CLI usage the config repository may not be bound yet.
            // Returning the default keeps the processor usable in those stripped-down contexts.
            return $default;
        }
    }
}
