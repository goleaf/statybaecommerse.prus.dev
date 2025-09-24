<?php

declare(strict_types=1);

namespace App\Services\ImportExport;

use App\Services\XmlCatalogService;

final class XmlProvider implements ProviderInterface
{
    public function __construct(private readonly XmlCatalogService $service) {}

    public function id(): string
    {
        return 'xml';
    }

    public function label(): string
    {
        return 'XML (lt/en)';
    }

    public function import(string $path, array $options = []): array
    {
        return $this->service->import($path, $options);
    }

    public function export(string $path, array $options = []): string
    {
        return $this->service->export($path, $options);
    }
}
