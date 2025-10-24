<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Brand;
use App\Support\Contracts\ContractPathResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class BrandContract
{
    public const CONTRACT = 'brand';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return ContractPathResolver::schema('brand.schema.json');
    }

    public static function examplePath(): string
    {
        return ContractPathResolver::example('brand.json');
    }

    public static function forBrand(Brand $brand, array $meta = []): array
    {
        $brandPayload = self::mapBrand($brand);

        return self::envelope([
            'brand' => $brandPayload,
            'item' => $brandPayload,
        ], $meta);
    }

    public static function forCollection(iterable $brands, array $meta = []): array
    {
        $paginator = $brands instanceof LengthAwarePaginator ? $brands : null;
        $items = $paginator?->getCollection() ?? Collection::make($brands);
        $mapped = $items->map(fn (Brand $brand): array => self::mapBrand($brand))->values()->all();

        $data = [
            'brands' => $mapped,
            'items' => $mapped,
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            // Surface pagination details alongside other metadata to respect the
            // published JSON schema that disallows arbitrary members under the
            // data payload for collection responses.
            $meta['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ];
            $meta['total'] = $paginator->total();
        } else {
            $meta['total'] = count($mapped);
        }

        return self::envelope($data, $meta);
    }

    private static function mapBrand(Brand $brand): array
    {
        return [
            'id'             => $brand->getKey(),
            'slug'           => (string) $brand->slug,
            'name'           => (string) $brand->name,
            'description'    => $brand->description,
            'website'        => $brand->website ? (string) $brand->website : null,
            'products_count' => $brand->products_count ?? null,
            'links'          => [
                'self' => route('brands.show', $brand->slug),
            ],
        ];
    }

    private static function envelope(array $data, array $meta = []): array
    {
        $meta = array_merge([
            'generated_at' => now()->toISOString(),
        ], Arr::whereNotNull($meta));

        return [
            'contract' => self::CONTRACT,
            'version'  => self::VERSION,
            'data'     => $data,
            'meta'     => $meta,
        ];
    }
}
