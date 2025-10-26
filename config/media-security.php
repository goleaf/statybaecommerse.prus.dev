<?php

declare(strict_types=1);

return [
    'disk'               => env('SECURE_MEDIA_DISK', 'secure-media'),
    'directory'          => env('SECURE_MEDIA_DIRECTORY', 'admin-uploads'),
    'max_size_kb'        => (int) env('SECURE_MEDIA_MAX_SIZE_KB', 5 * 1024),
    'url_lifetime'       => (int) env('SECURE_MEDIA_URL_LIFETIME', 30),
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
        'image/gif',
        'image/svg+xml',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'text/xml',
        'application/xml',
    ],
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'avif',
        'gif',
        'svg',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'csv',
        'txt',
        'xml',
    ],
];
