<?php

declare(strict_types=1);

namespace App\Services\Export\Contracts;

use App\Services\Export\ExportColumn;

interface DefinesExportColumns
{
    /**
     * @return array<string, ExportColumn>
     */
    public static function availableExportColumns(): array;
}
