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

    'binaries' => [
        'tar' => env('BACKUP_TAR_BINARY', 'tar'),
        'git' => env('BACKUP_GIT_BINARY', 'git'),
        'mysqldump' => env('BACKUP_MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql' => env('BACKUP_MYSQL_BINARY', 'mysql'),
        'pg_dump' => env('BACKUP_PG_DUMP_BINARY', 'pg_dump'),
        'psql' => env('BACKUP_PSQL_BINARY', 'psql'),
        'sqlite3' => env('BACKUP_SQLITE3_BINARY', 'sqlite3'),
    ],

    'archive' => [
        'create_flags' => env('BACKUP_TAR_CREATE_FLAGS', '-czf'),
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
        'connection' => env('BACKUP_VERIFY_CONNECTION', 'sqlite'),
    ],
];
