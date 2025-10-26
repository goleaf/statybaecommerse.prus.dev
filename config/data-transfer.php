<?php

declare(strict_types=1);

return [
    'disk'         => env('DATA_TRANSFER_DISK', 'local'),
    'exports_path' => 'exports',
    'imports_path' => 'imports',
    'log_channel'  => env('DATA_TRANSFER_LOG_CHANNEL', 'maintenance'),

    'contracts' => [
        'user_profiles' => \App\DataTransfer\UserProfilesDataTransfer::class,
    ],
];
