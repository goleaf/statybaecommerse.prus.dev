<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;

final class BrandCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function __construct(private readonly ProductCatalogueDataProvider $products) {}

    public function index(): array
    {
        $brands = Brand::query()
            ->withCount([
                'products as visible_products_count' => static function (Builder $query): void {
                    $query->where('is_visible', true)
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                },
            ])
            ->orderBy('name')
            ->get();

        return [
            'brands' => $brands,
        ];
    }

    public function show(Brand $brand, array $filters = []): array
    {
        $brand->loadMissing(['products']);

        $listing = $this->products->listing(
            $filters,
            static function (Builder $query) use ($brand): void {
                $query->where('brand_id', $brand->getKey());
            }
        );

        return array_merge($listing, [
            'brand' => $brand,
        ]);
    }
}
