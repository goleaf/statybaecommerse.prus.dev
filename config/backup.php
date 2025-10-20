<?php

declare(strict_types=1);

return [
    'storage_root' => env('BACKUP_STORAGE_ROOT', storage_path('app/backups')),
    'media_paths' => array_values(array_filter(array_map(
        static fn (string $path): string => trim($path),
        explode(',', (string) env('BACKUP_MEDIA_PATHS', storage_path('app/public'))),
    ))),
    'database' => [
        'connection' => env('BACKUP_DB_CONNECTION', config('database.default')),
        'dump_binary' => env('BACKUP_DB_DUMP_BINARY'),
        'restore_binary' => env('BACKUP_DB_RESTORE_BINARY'),
    ],
    'verify' => [
        'connection' => env('BACKUP_VERIFY_CONNECTION'),
        'database' => env('BACKUP_VERIFY_DATABASE'),
        'temp_root' => env('BACKUP_VERIFY_TEMP_ROOT', storage_path('app/backup-verification')),
    ],
    'schedule' => [
        'prepare' => env('BACKUP_PREPARE_SCHEDULE'),
        'verify' => env('BACKUP_VERIFY_SCHEDULE'),
    ],
];
