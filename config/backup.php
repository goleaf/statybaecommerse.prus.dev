<?php

declare(strict_types=1);

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;

$defaultMediaPaths = [
    storage_path('app/public'),
];

return [
    'storage_path' => env('BACKUP_STORAGE_PATH', storage_path('app/backups')),

    'connection' => env('BACKUP_DATABASE_CONNECTION', env('DB_CONNECTION', 'sqlite')),

    'media_paths' => [
        storage_path('app/public'),
    ],

    'repositories' => [
        'users'    => UserRepository::class,
        'products' => ProductRepository::class,
    ],

    'binaries' => [
        'tar'       => env('BACKUP_TAR_BINARY', 'tar'),
        'git'       => env('BACKUP_GIT_BINARY', 'git'),
        'mysqldump' => env('BACKUP_MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql'     => env('BACKUP_MYSQL_BINARY', 'mysql'),
        'pg_dump'   => env('BACKUP_PG_DUMP_BINARY', 'pg_dump'),
        'psql'      => env('BACKUP_PSQL_BINARY', 'psql'),
        'sqlite3'   => env('BACKUP_SQLITE3_BINARY', 'sqlite3'),
    ],

    'archive' => [
        'create_flags'  => env('BACKUP_TAR_CREATE_FLAGS', '-czf'),
        'extract_flags' => env('BACKUP_TAR_EXTRACT_FLAGS', '-xzf'),
    ],

    'dump' => [
        'mysql' => [
            'options' => env('BACKUP_MYSQLDUMP_OPTIONS', '--single-transaction --routines --events'),
        ],
        'pgsql' => [
            'options' => env('BACKUP_PG_DUMP_OPTIONS', '--no-owner --no-privileges'),
        ],
    ],

    'verify' => [
        'working_path'    => env('BACKUP_VERIFY_WORKING_PATH', storage_path('app/backup-verify')),
        'connection_name' => env('BACKUP_VERIFY_CONNECTION', 'backup-verify'),
        'connection'      => [
            'driver'                  => 'sqlite',
            'database'                => env('BACKUP_VERIFY_DATABASE', storage_path('app/backup-verify/database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ],
    ],

    'schedule' => [
        'prepare' => [
            'enabled' => filter_var((string) env('BACKUP_PREPARE_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
            'at'      => env('BACKUP_PREPARE_AT', '01:30'),
            'cron'    => env('BACKUP_PREPARE_CRON'),
        ],
        'verify' => [
            'enabled' => filter_var((string) env('BACKUP_VERIFY_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
            'at'      => env('BACKUP_VERIFY_AT', '02:30'),
            'cron'    => env('BACKUP_VERIFY_CRON'),
        ],
    ],

];
