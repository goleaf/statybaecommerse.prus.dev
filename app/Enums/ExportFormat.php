<?php

declare(strict_types=1);

namespace App\Enums;

enum ExportFormat: string
{
    case CSV = 'csv';
    case XLSX = 'xlsx';
    case PDF = 'pdf';

    public function extension(): string
    {
        return $this->value;
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::CSV => 'text/csv',
            self::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::PDF => 'application/pdf',
        };
    }
}
