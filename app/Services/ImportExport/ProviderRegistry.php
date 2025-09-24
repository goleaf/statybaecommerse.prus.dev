<?php

declare(strict_types=1);

namespace App\Services\ImportExport;

use App\Services\XmlCatalogService;

final class ProviderRegistry
{
    /**
     * @return array<string, ProviderInterface>
     */
    public static function providers(): array
    {
        return [
            'xml' => new XmlProvider(app(XmlCatalogService::class)),
        ];
    }

    public static function get(string $id): ?ProviderInterface
    {
        $all = self::providers();

        return $all[$id] ?? null;
    }
}
