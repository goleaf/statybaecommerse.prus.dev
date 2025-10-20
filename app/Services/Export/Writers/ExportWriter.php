<?php

declare(strict_types=1);

namespace App\Services\Export\Writers;

use App\Models\Export;

interface ExportWriter
{
    /**
     * @param  array<int, string>  $headers
     */
    public function open(Export $export, array $headers): void;

    /**
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function appendRows(iterable $rows): void;

    public function close(): string;
}
