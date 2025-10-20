<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use App\Services\Export\Contracts\ExportWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class PdfExportWriter implements ExportWriter
{
    private array $rows = [];

    private array $columns = [];

    private ?Export $export = null;

    private string $path = '';

    public function open(Export $export, array $columns, string $path): void
    {
        $this->export = $export;
        $this->columns = $columns;
        $this->path = $path;

        Storage::disk('local')->makeDirectory(dirname($path));
    }

    public function append(iterable $rows): void
    {
        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
    }

    public function close(): void
    {
        if (! $this->export instanceof Export) {
            return;
        }

        $pdf = Pdf::loadView('exports.table', [
            'export' => $this->export,
            'columns' => $this->columns,
            'rows' => $this->rows,
        ])->setPaper('a4', 'landscape');

        Storage::disk('local')->put($this->path, $pdf->output());

        $this->export = null;
        $this->rows = [];
        $this->columns = [];
    }

    public function extension(): string
    {
        return 'pdf';
    }

    public function mimeType(): string
    {
        return 'application/pdf';
    }
}
