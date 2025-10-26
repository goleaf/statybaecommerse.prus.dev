<?php

declare(strict_types=1);

$csvToArray = static function (mixed $value): array {
    if (! is_string($value) || $value === '') {
        return [];
    }

    $segments = array_map('trim', explode(',', $value));

    return array_values(array_filter(
        array_map(
            static fn (string $segment): ?string => $segment !== '' ? $segment : null,
            $segments
        )
    ));
};

$resolvedDsn = (string) env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN', ''));

return [
    'enabled' => (bool) env('SENTRY_ENABLED', $resolvedDsn !== ''),

    'dsn' => $resolvedDsn !== '' ? $resolvedDsn : null,

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    'release' => env('SENTRY_RELEASE'),

    'dist' => env('SENTRY_DIST'),

    'send_default_pii' => (bool) env('SENTRY_SEND_DEFAULT_PII', false),

    'attach_stacktrace' => (bool) env('SENTRY_ATTACH_STACKTRACE', true),

    'capture_silenced_errors' => (bool) env('SENTRY_CAPTURE_SILENCED_ERRORS', false),

    'report_deprecations' => (bool) env('SENTRY_REPORT_DEPRECATIONS', false),

    'max_breadcrumbs' => (int) env('SENTRY_MAX_BREADCRUMBS', 100),

    'send_attempts' => (int) env('SENTRY_SEND_ATTEMPTS', 4),

    'monolog_level' => env('SENTRY_LOG_LEVEL', env('LOG_LEVEL', 'error')),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    'send_client_reports' => (bool) env('SENTRY_SEND_CLIENT_REPORTS', true),

    'attachments' => (bool) env('SENTRY_ATTACHMENTS_ENABLED', false),

    'ignore_exceptions' => $csvToArray(env('SENTRY_IGNORE_EXCEPTIONS')),

    'in_app_exclude' => $csvToArray(env('SENTRY_IN_APP_EXCLUDE')),

    'breadcrumbs' => [
        'logs'         => (bool) env('SENTRY_BREADCRUMBS_LOGS_ENABLED', true),
        'sql_queries'  => (bool) env('SENTRY_BREADCRUMBS_SQL_QUERIES_ENABLED', true),
        'queue_info'   => (bool) env('SENTRY_BREADCRUMBS_QUEUE_INFO_ENABLED', true),
        'command_info' => (bool) env('SENTRY_BREADCRUMBS_COMMAND_INFO_ENABLED', true),
    ],
];
