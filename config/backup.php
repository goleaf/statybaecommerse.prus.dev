<?php

declare(strict_types=1);

$mediaPaths = env('BACKUP_MEDIA_PATHS');

return [
    'database_connection' => env('BACKUP_DATABASE_CONNECTION'),
    'process_timeout' => (int) env('BACKUP_PROCESS_TIMEOUT', 300),
    'media_paths' => $mediaPaths !== null
        ? array_values(array_filter(array_map('trim', explode(',', (string) $mediaPaths))))
        : [storage_path('app/public')],
    'commands' => [
        'dump' => [
            'mysql' => env('BACKUP_DUMP_COMMAND_MYSQL', 'mysqldump'),
            'pgsql' => env('BACKUP_DUMP_COMMAND_PGSQL', 'pg_dump'),
        ],
        'restore' => [
            'mysql' => env('BACKUP_RESTORE_COMMAND_MYSQL', 'mysql'),
            'pgsql' => env('BACKUP_RESTORE_COMMAND_PGSQL', 'psql'),
        ],
    ],
    'verify' => [
        'connection' => env('BACKUP_VERIFY_CONNECTION'),
        'database' => env('BACKUP_VERIFY_DATABASE'),
        'maintenance_database' => env('BACKUP_VERIFY_MAINTENANCE_DATABASE', 'postgres'),
    ],
    'schedule' => [
        'prepare' => env('BACKUP_SCHEDULE_PREPARE_CRON'),
        'verify' => env('BACKUP_SCHEDULE_VERIFY_CRON'),
    ],
];
