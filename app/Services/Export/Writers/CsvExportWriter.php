<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use App\Services\Export\Contracts\ExportWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class CsvExportWriter implements ExportWriter
{
    /** @var resource|null */
    private $handle;

    private string $path;

    private array $columns = [];

    public function open(Export $export, array $columns, string $path): void
    {
        $this->columns = $columns;
        $this->path = $path;

        Storage::disk('local')->makeDirectory(dirname($path));

        $fullPath = Storage::disk('local')->path($path);

        $this->handle = fopen($fullPath, 'wb');

        if ($this->handle === false) {
            throw new RuntimeException('Unable to open CSV file for writing.');
        }

        fputcsv($this->handle, array_column($columns, 'label'));
    }

    public function append(iterable $rows): void
    {
        if (! is_resource($this->handle)) {
            throw new RuntimeException('CSV handle is not available.');
        }

        foreach ($rows as $row) {
            fputcsv($this->handle, array_values($row));
        }
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        $this->handle = null;
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function mimeType(): string
    {
        return 'text/csv';
    }
}
