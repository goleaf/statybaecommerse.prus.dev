<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use Illuminate\Support\Facades\Storage;

final class CsvExportWriter implements ExportWriter
{
    private $handle;

    private string $path;

    public function open(Export $export, array $headers): void
    {
        $disk = Storage::disk(config('export.disk'));
        $filename = sprintf('%s.%s', $export->id, $export->format->extension());
        $this->path = 'exports/'.$filename;
        $disk->makeDirectory('exports');
        $fullPath = $disk->path($this->path);
        $this->handle = fopen($fullPath, 'w');
        fputcsv($this->handle, $headers);
    }

    public function appendRows(iterable $rows): void
    {
        foreach ($rows as $row) {
            fputcsv($this->handle, array_map(fn ($value) => is_scalar($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR), $row));
        }
    }

    public function close(): string
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        return $this->path;
    }
}
