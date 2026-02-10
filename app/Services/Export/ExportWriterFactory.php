<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Enums\ExportFormat;
use App\Services\Export\Contracts\ExportWriter;
use App\Services\Export\Writers\CsvExportWriter;

final class ExportWriterFactory
{
    public function make(ExportFormat $format): ExportWriter
    {
        return match ($format) {
            ExportFormat::CSV => new CsvExportWriter,
        };
    }
}
