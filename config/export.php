<?php

declare(strict_types=1);

return [
    'disk' => env('EXPORT_DISK', 'exports'),
    'chunk_size' => (int) env('EXPORT_CHUNK_SIZE', 500),
    'retention_hours' => (int) env('EXPORT_RETENTION_HOURS', 48),
    'download_url_ttl' => (int) env('EXPORT_DOWNLOAD_TTL', 60),
];
