<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Services\Export\ExportFormat;
use InvalidArgumentException;

final class ExportWriterFactory
{
    public function make(ExportFormat $format): ExportWriter
    {
        return match ($format) {
            ExportFormat::Csv  => new CsvExportWriter,
            ExportFormat::Xlsx => new XlsxExportWriter,
            ExportFormat::Pdf  => new PdfExportWriter,
            default            => throw new InvalidArgumentException('Unsupported export format: ' . $format->value),
        };
    }
}
