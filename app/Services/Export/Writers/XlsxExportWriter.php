<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use App\Services\Export\Contracts\ExportWriter;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

final class XlsxExportWriter implements ExportWriter
{
    private ?SimpleExcelWriter $writer = null;

    public function open(Export $export, array $columns, string $path): void
    {
        Storage::disk('local')->makeDirectory(dirname($path));

        $fullPath = Storage::disk('local')->path($path);

        $this->writer = SimpleExcelWriter::create($fullPath);
        $this->writer->addHeader(array_column($columns, 'label'));
    }

    public function append(iterable $rows): void
    {
        if (! $this->writer instanceof SimpleExcelWriter) {
            return;
        }

        foreach ($rows as $row) {
            $this->writer->addRow(array_values($row));
        }
    }

    public function close(): void
    {
        $this->writer?->close();
        $this->writer = null;
    }

    public function extension(): string
    {
        return 'xlsx';
    }

    public function mimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
}
