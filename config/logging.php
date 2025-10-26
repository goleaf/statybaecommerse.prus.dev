<?php

declare(strict_types=1);

use App\Logging\CustomizeFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

$dsnRaw = env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN', ''));
$sentryDsn = is_string($dsnRaw) ? $dsnRaw : '';
$sentryAvailable = $sentryDsn !== '' && class_exists(\Sentry\Laravel\Integration::class);

$rawStack = env('LOG_STACK', 'daily');
$stackString = is_string($rawStack) ? $rawStack : 'daily';
$configuredStackChannels = array_filter(array_map(
    static fn (string $channel): ?string => $channel !== '' ? $channel : null,
    explode(',', $stackString)
));

// Retention is configured directly in channel definitions below.

$stackChannels = $configuredStackChannels === []
    ? ['daily']
    : array_values(array_unique($configuredStackChannels));

$productionStackChannels = array_values(array_unique(array_merge(['daily'], $stackChannels)));

if ($sentryAvailable) {
    $stackChannels = array_values(array_unique(array_merge($stackChannels, ['sentry'])));
    $productionStackChannels = array_values(array_unique(array_merge($productionStackChannels, ['sentry'])));
}
$isProductionEnvironment = env('APP_ENV', 'production') === 'production';

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', $isProductionEnvironment ? 'production' : 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver'            => 'stack',
            'channels'          => $stackChannels,
            'ignore_exceptions' => false,
            'tap'               => [App\Logging\ConfigureContextProcessors::class],
        ],

        'production' => [
            'driver'            => 'stack',
            'channels'          => $productionStackChannels,
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver'               => 'single',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'formatter'            => JsonFormatter::class,
            'formatter_with'       => [
                'batch_mode'     => JsonFormatter::BATCH_MODE_JSON,
                'append_newline' => true,
            ],
            'tap' => [App\Logging\ConfigureContextProcessors::class],
        ],

        'daily' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'days'                 => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
            'tap'                  => [
                CustomizeFormatter::class,
            ],
        ],

        'security' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/security.log'),
            'level'                => env('LOG_SECURITY_LEVEL', 'notice'),
            'days'                 => env('LOG_SECURITY_DAYS', 30),
            'replace_placeholders' => true,
            'tap'                  => [
                CustomizeFormatter::class,
            ],
        ],

        'slack' => [
            'driver'               => 'slack',
            'url'                  => env('LOG_SLACK_WEBHOOK_URL'),
            'username'             => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji'                => env('LOG_SLACK_EMOJI', ':boom:'),
            'level'                => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host'             => env('PAPERTRAIL_URL'),
                'port'             => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
            'tap'        => [App\Logging\ConfigureContextProcessors::class],
        ],

        'stderr' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter'  => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
            'tap'        => [App\Logging\ConfigureContextProcessors::class],
        ],

        'sentry' => [
            'driver' => 'sentry',
            'level'  => env('SENTRY_LOG_LEVEL', env('LOG_LEVEL', 'error')),
        ],

        'syslog' => [
            'driver'               => 'syslog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'facility'             => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver'               => 'errorlog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'maintenance' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/maintenance.log'),
            'level'                => env('LOG_MAINTENANCE_LEVEL', 'info'),
            'days'                 => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
            'formatter'            => JsonFormatter::class,
            'formatter_with'       => [
                'batch_mode'     => JsonFormatter::BATCH_MODE_JSON,
                'append_newline' => true,
            ],
            'tap' => [App\Logging\ConfigureContextProcessors::class],
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
