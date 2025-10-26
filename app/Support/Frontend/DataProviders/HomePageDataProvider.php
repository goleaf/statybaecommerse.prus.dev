<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class HomePageDataProvider
{
    public function __construct(private readonly ProductCatalogueDataProvider $products) {}

    public function get(): array
    {
        $locale = app()->getLocale();

        $stats = Cache::remember("frontend:home:stats:{$locale}", now()->addMinutes(1), function (): array {
            return [
                'products_count'   => $this->countPublishedProducts(),
                'categories_count' => Category::query()->count(),
                'brands_count'     => $this->countVisibleBrands(),
                'reviews_count'    => Review::query()->where('is_approved', true)->count(),
                'avg_rating'       => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0.0),
            ];
        });

        return [
            'stats'             => $stats,
            'featuredProducts'  => $this->products->featured(),
            'latestProducts'    => $this->products->latest(),
            'trendingProducts'  => $this->products->trending(),
            'saleProducts'      => $this->products->onSale(),
            'topCategories'     => $this->collectTopCategories(),
            'highlightedBrands' => $this->collectHighlightedBrands(),
        ];
    }

    private function countPublishedProducts(): int
    {
        return Product::query()->published()->count();
    }

    private function countVisibleBrands(): int
    {
        return Brand::query()->where('is_visible', true)->count();
    }

    private function collectTopCategories(): Collection
    {
        return $this->products->categoryHighlights();
    }

    private function collectHighlightedBrands(): Collection
    {
        return $this->products->brandHighlights();
    }
}
