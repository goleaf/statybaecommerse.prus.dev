<?php

declare(strict_types=1);

namespace App\Services\Export;

enum ExportFormat: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';

    public function extension(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Csv  => 'CSV',
            self::Xlsx => 'Excel (XLSX)',
            self::Pdf  => 'PDF',
        };
    }
}
