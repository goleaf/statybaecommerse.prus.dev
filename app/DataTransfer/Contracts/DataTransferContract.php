<?php

declare(strict_types=1);

namespace App\DataTransfer\Contracts;

use Illuminate\Filesystem\FilesystemAdapter;

interface DataTransferContract
{
    /**
     * @return array<int, string>
     */
    public function supportedFormats(): array;

    public function export(string $format, FilesystemAdapter $disk, string $path): void;

    public function import(string $format, FilesystemAdapter $disk, string $path): void;
}
