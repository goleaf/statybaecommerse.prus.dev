<?php

return [
    'disk' => env('BACKUP_DISK', env('FILESYSTEM_DISK', 'local')),
    'directory' => env('BACKUP_DIRECTORY', 'backups'),
    'verify' => [
        'connection' => env('BACKUP_VERIFY_CONNECTION', 'sqlite'),
    ],
];
