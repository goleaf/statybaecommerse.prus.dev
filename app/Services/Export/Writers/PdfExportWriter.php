<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Services\Export\Contracts\ExportWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class PdfExportWriter implements ExportWriter
{
    private string $disk;

    private string $path;

    /**
     * @var array<int, string>
     */
    private array $lines = [];

    public function open(string $disk, string $path, array $headers): void
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->lines[] = implode(' | ', $headers);
    }

    public function append(array $row): void
    {
        $this->lines[] = implode(' | ', $row);
    }

    public function close(): void
    {
        $pdf = $this->buildPdf($this->lines);
        Storage::disk($this->disk)->put($this->path, $pdf);
    }

    /**
     * @param array<int, string> $lines
     */
    private function buildPdf(array $lines): string
    {
        $contentStream = $this->buildContentStream($lines);
        $objects = [];

        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        $objects[] = sprintf("4 0 obj\n<< /Length %d >>\nstream\n%s\nendstream\nendobj\n", strlen($contentStream), $contentStream);
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        $position = strlen($pdf);

        foreach ($objects as $object) {
            $offsets[] = $position;
            $pdf .= $object;
            $position = strlen($pdf);
        }

        $xrefPosition = $position;
        $pdf .= sprintf("xref\n0 %d\n", count($offsets));
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1, $count = count($offsets); $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n";
        $pdf .= sprintf("<< /Size %d /Root 1 0 R >>\n", count($offsets));
        $pdf .= "startxref\n" . $xrefPosition . "\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    /**
     * @param array<int, string> $lines
     */
    private function buildContentStream(array $lines): string
    {
        $chunks = ['BT', '/F1 10 Tf', '72 760 Td'];

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $chunks[] = '0 -14 Td';
            }

            $escaped = Str::of($line)->replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)']);
            $chunks[] = sprintf('(%s) Tj', $escaped);
        }

        $chunks[] = 'ET';

        return implode("\n", $chunks);
    }
}
