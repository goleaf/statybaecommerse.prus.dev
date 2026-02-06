<?php

declare(strict_types=1);

namespace App\Enums;

enum ExportFormat: string
{
    case CSV = 'csv';

    public function extension(): string
    {
        return $this->value;
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::CSV => 'text/csv',
        };
    }
}
