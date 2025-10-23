<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class XlsxExportWriter implements ExportWriter
{
    private Spreadsheet $spreadsheet;

    private int $rowPointer = 1;

    private string $path;

    public function open(Export $export, array $headers): void
    {
        $this->spreadsheet = new Spreadsheet;
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $this->rowPointer = 2;
        $this->path = sprintf('exports/%s.%s', $export->id, $export->format->extension());
    }

    public function appendRows(iterable $rows): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        foreach ($rows as $row) {
            $sheet->fromArray([$row], null, 'A'.$this->rowPointer);
            $this->rowPointer++;
        }
    }

    public function close(): string
    {
        $disk = Storage::disk(config('export.disk'));
        $disk->makeDirectory('exports');
        $fullPath = $disk->path($this->path);
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($fullPath);
        $this->spreadsheet->disconnectWorksheets();

        return $this->path;
    }
}
