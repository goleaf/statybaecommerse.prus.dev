<?php

declare(strict_types=1);

return [
    'metrics' => [
        'cache_store' => env('OBSERVABILITY_METRICS_CACHE_STORE', env('CACHE_STORE', 'database')),
        'cache_key'   => env('OBSERVABILITY_METRICS_CACHE_KEY', 'monitoring:cache_metrics'),
        'queue_key'   => env('OBSERVABILITY_QUEUE_METRICS_CACHE_KEY', 'monitoring:queue_metrics'),
    ],
    'tracing' => [
        'enabled'           => (bool) env('TELEMETRY_ENABLED', false),
        'service_name'      => env('OTEL_SERVICE_NAME', env('APP_NAME', 'laravel')),
        'service_namespace' => env('OTEL_SERVICE_NAMESPACE', 'statybaecommerse'),
        'sampler_ratio'     => (float) env('OTEL_TRACES_SAMPLER_RATIO', 1.0),
        'otlp'              => [
            'endpoint'    => env('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://localhost:4318/v1/traces'),
            'headers'     => env('OTEL_EXPORTER_OTLP_HEADERS', ''),
            'compression' => env('OTEL_EXPORTER_OTLP_COMPRESSION'),
            'timeout'     => (float) env('OTEL_EXPORTER_OTLP_TIMEOUT', 10.0),
        ],
    ],
];
