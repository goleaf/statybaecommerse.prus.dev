<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class PdfExportWriter implements ExportWriter
{
    private array $headers = [];

    private array $rows = [];

    private string $path;

    public function open(Export $export, array $headers): void
    {
        $this->headers = $headers;
        $this->path = sprintf('exports/%s.%s', $export->id, $export->format->extension());
    }

    public function appendRows(iterable $rows): void
    {
        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
    }

    public function close(): string
    {
        $disk = Storage::disk(config('export.disk'));
        $disk->makeDirectory('exports');
        $pdf = Pdf::loadView('exports.table', [
            'headers' => $this->headers,
            'rows' => $this->rows,
        ]);
        $disk->put($this->path, $pdf->output());

        return $this->path;
    }
}
