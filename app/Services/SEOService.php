<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

final class SEOService
{
    public static function getProductSEO(Product $product): array
    {
        $title = $product->meta_title ?? $product->name.' - '.config('app.name');
        $description = $product->meta_description ?? Str::limit(strip_tags($product->description), 160);
        $keywords = $product->meta_keywords ?? self::generateProductKeywords($product);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => route('product.show', $product->slug),
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $product->getFirstMediaUrl('images', 'image-lg') ?: $product->getFirstMediaUrl('images'),
            'og_type' => 'product',
            'product_price' => number_format($product->price, 2),
            'product_currency' => 'EUR',
            'product_availability' => $product->stock_quantity > 0 ? 'in stock' : 'out of stock',
        ];
    }

    public static function getCategorySEO(Category $category): array
    {
        $title = $category->meta_title ?? $category->name.' - '.config('app.name');
        $description = $category->meta_description ?? Str::limit(strip_tags($category->description), 160);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => route('category.show', ['category' => $category->slug]),
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $category->getFirstMediaUrl('images', 'image-lg') ?: $category->getFirstMediaUrl('images'),
            'og_type' => 'website',
        ];
    }

    public static function getBrandSEO(Brand $brand): array
    {
        $title = $brand->meta_title ?? $brand->name.' Products - '.config('app.name');
        $description = $brand->meta_description ?? Str::limit(strip_tags($brand->description), 160);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => route('brands.show', $brand->slug),
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $brand->getFirstMediaUrl('logo', 'logo-md') ?: $brand->getFirstMediaUrl('logo'),
            'og_type' => 'website',
        ];
    }

    private static function generateProductKeywords(Product $product): string
    {
        $keywords = collect([
            $product->name,
            $product->brand?->name,
            $product->categories->first()?->name,
            $product->sku,
        ])
            ->filter()
            ->map(fn ($keyword) => Str::slug($keyword, ' '))
            ->implode(', ');

        return $keywords;
    }

    public static function getStructuredData(Product $product): array
    {
        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => strip_tags($product->description),
            'sku' => $product->sku,
            'image' => $product->getFirstMediaUrl('images', 'image-lg') ?: $product->getFirstMediaUrl('images'),
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand?->name ?? config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => number_format($product->price, 2),
                'priceCurrency' => 'EUR',
                'availability' => $product->stock_quantity > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
            ],
        ];
    }
}
