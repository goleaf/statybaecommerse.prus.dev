<?php

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

    'verify' => [
        'connection' => env('BACKUP_VERIFY_CONNECTION', 'sqlite'),
    ],
];
