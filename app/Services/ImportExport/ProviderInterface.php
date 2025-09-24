<?php

declare(strict_types=1);

namespace App\Services\ImportExport;

interface ProviderInterface
{
    public function id(): string;

    public function label(): string;

    public function import(string $path, array $options = []): array;

    public function export(string $path, array $options = []): string;
}
