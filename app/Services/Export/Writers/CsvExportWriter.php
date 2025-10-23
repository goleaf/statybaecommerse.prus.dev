<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Services\Export\Contracts\ExportWriter;
use Illuminate\Support\Facades\Storage;

final class CsvExportWriter implements ExportWriter
{
    private const DELIMITER = ',';

    private const ENCLOSURE = '"';

    private $handle;

    private string $disk;

    private string $path;

    public function open(string $disk, string $path, array $headers): void
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->handle = fopen('php://temp', 'r+');
        fputcsv($this->handle, $headers, self::DELIMITER, self::ENCLOSURE);
    }

    public function append(array $row): void
    {
        fputcsv($this->handle, $row, self::DELIMITER, self::ENCLOSURE);
    }

    public function close(): void
    {
        if (! is_resource($this->handle)) {
            return;
        }

        rewind($this->handle);
        $contents = stream_get_contents($this->handle) ?: '';
        fclose($this->handle);
        Storage::disk($this->disk)->put($this->path, $contents);
    }
}
