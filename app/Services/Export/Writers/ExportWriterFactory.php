<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Services\Export\ExportFormat;

final class ExportWriterFactory
{
    public function make(ExportFormat $format): ExportWriter
    {
        return match ($format) {
            ExportFormat::Csv => new CsvExportWriter,
        };
    }
}
