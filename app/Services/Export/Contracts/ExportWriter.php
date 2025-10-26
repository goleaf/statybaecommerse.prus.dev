<?php

declare(strict_types=1);

namespace App\Services\Export\Contracts;

interface ExportWriter
{
    /**
     * @param array<int, string> $headers
     */
    public function open(string $disk, string $path, array $headers): void;

    /**
     * @param array<int, string> $row
     */
    public function append(array $row): void;

    /**
     * Finalize the export and persist buffered data to storage.
     */
    public function close(): void;
}
