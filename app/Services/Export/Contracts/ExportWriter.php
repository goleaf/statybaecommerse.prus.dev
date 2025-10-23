<?php

declare(strict_types=1);

namespace App\Services\Export\Contracts;

use App\Models\Export;

interface ExportWriter
{
    public function open(Export $export, array $columns, string $path): void;

    /**
     * @param iterable<int, array<string, mixed>> $rows
     */
    public function append(iterable $rows): void;

    public function close(): void;

    public function extension(): string;

    public function mimeType(): string;
}
