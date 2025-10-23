<?php

declare(strict_types=1);

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Str;

$defaultMediaPaths = [
    storage_path('app/public'),
];

return [
    'storage_path' => env('BACKUP_STORAGE_PATH', storage_path('app/backups')),

    'connection' => env('BACKUP_DATABASE_CONNECTION', env('DB_CONNECTION', 'sqlite')),

    'media_paths' => value(function () use ($defaultMediaPaths) {
        $raw = env('BACKUP_MEDIA_PATHS');

        if ($raw === null) {
            return $defaultMediaPaths;
        }

        $segments = preg_split('/[,;\n]+/', (string) $raw) ?: [];
        $paths = array_filter(array_map('trim', $segments));

        return array_map(static function (string $path): string {
            return Str::startsWith($path, DIRECTORY_SEPARATOR)
                ? $path
                : base_path($path);
        }, $paths);
    }),

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
        'connection'      => value(function () {
            $driver = env('BACKUP_VERIFY_DRIVER', 'sqlite');

            return match ($driver) {
                'sqlite' => [
                    'driver'                  => 'sqlite',
                    'database'                => env('BACKUP_VERIFY_DATABASE', storage_path('app/backup-verify/database.sqlite')),
                    'prefix'                  => '',
                    'foreign_key_constraints' => true,
                ],
                default => [
                    'driver'      => $driver,
                    'host'        => env('BACKUP_VERIFY_HOST'),
                    'port'        => env('BACKUP_VERIFY_PORT'),
                    'database'    => env('BACKUP_VERIFY_DATABASE'),
                    'username'    => env('BACKUP_VERIFY_USERNAME'),
                    'password'    => env('BACKUP_VERIFY_PASSWORD'),
                    'unix_socket' => env('BACKUP_VERIFY_SOCKET'),
                    'charset'     => env('BACKUP_VERIFY_CHARSET', 'utf8mb4'),
                    'collation'   => env('BACKUP_VERIFY_COLLATION', 'utf8mb4_unicode_ci'),
                    'prefix'      => '',
                    'strict'      => true,
                ],
            };
        }),
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
