<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Enums\ExportFormat;
use App\Services\Export\Contracts\ExportWriter;
use App\Services\Export\Writers\CsvExportWriter;
use App\Services\Export\Writers\PdfExportWriter;
use App\Services\Export\Writers\XlsxExportWriter;
use InvalidArgumentException;

final class ExportWriterFactory
{
    public function make(ExportFormat $format): ExportWriter
    {
        return match ($format) {
            ExportFormat::CSV => new CsvExportWriter(),
            ExportFormat::XLSX => new XlsxExportWriter(),
            ExportFormat::PDF => new PdfExportWriter(),
            default => throw new InvalidArgumentException("Unsupported export format: {$format->value}"),
        };
    }
}
