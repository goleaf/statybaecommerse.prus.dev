<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Services\Export\Contracts\ExportWriter;
use Illuminate\Support\Facades\Storage;

final class XlsxExportWriter implements ExportWriter
{
    private string $disk;

    private string $path;

    /**
     * @var array<int, array<int, string>>
     */
    private array $rows = [];

    public function open(string $disk, string $path, array $headers): void
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->rows = [$headers];
    }

    public function append(array $row): void
    {
        $this->rows[] = $row;
    }

    public function close(): void
    {
        $xml = $this->buildSpreadsheetXml($this->rows);
        Storage::disk($this->disk)->put($this->path, $xml);
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function buildSpreadsheetXml(array $rows): string
    {
        $document = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">',
            '  <Worksheet ss:Name="Export">',
            '    <Table>',
        ];

        foreach ($rows as $row) {
            $document[] = '      <Row>';
            foreach ($row as $cell) {
                $escaped = htmlspecialchars($cell ?? '', ENT_XML1 | ENT_COMPAT, 'UTF-8');
                $document[] = sprintf('        <Cell><Data ss:Type="String">%s</Data></Cell>', $escaped);
            }
            $document[] = '      </Row>';
        }

        $document[] = '    </Table>';
        $document[] = '  </Worksheet>';
        $document[] = '</Workbook>';

        return implode("\n", $document);
    }
}
