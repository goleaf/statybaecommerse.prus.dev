<?php

declare(strict_types=1);

return [
    'disk'             => env('EXPORT_DISK', env('FILESYSTEM_DISK', 'public')),
    'chunk_size'       => (int) env('EXPORT_CHUNK_SIZE', 250),
    'download_url_ttl' => (int) env('EXPORT_DOWNLOAD_URL_TTL', 60),
    'formats'          => [
        'csv'  => \App\Services\Export\Writers\CsvExportWriter::class,
        'xlsx' => \App\Services\Export\Writers\XlsxExportWriter::class,
        'pdf'  => \App\Services\Export\Writers\PdfExportWriter::class,
    ],
];
