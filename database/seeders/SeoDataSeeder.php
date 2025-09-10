<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Illuminate\Database\Seeder;

final class SeoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('🌐 Seeding SEO data...');

        $locales = ['lt', 'en'];

        $createFor = static function (string $type, int $id, array $attributes) use ($locales): void {
            foreach ($locales as $locale) {
                SeoData::updateOrCreate(
                    [
                        'seoable_type' => $type,
                        'seoable_id' => $id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $attributes['title'][$locale] ?? $attributes['title']['lt'] ?? null,
                        'description' => $attributes['description'][$locale] ?? $attributes['description']['lt'] ?? null,
                        'keywords' => $attributes['keywords'][$locale] ?? ($attributes['keywords']['lt'] ?? null),
                        'canonical_url' => $attributes['canonical_url'][$locale] ?? $attributes['canonical_url']['lt'] ?? null,
                        'meta_tags' => $attributes['meta_tags'][$locale] ?? [],
                        'structured_data' => $attributes['structured_data'][$locale] ?? [],
                        'no_index' => false,
                        'no_follow' => false,
                    ]
                );
            }
        };

        Product::query()->limit(50)->get()->each(function (Product $product) use ($createFor): void {
            $createFor(Product::class, $product->id, [
                'title' => [
                    'lt' => mb_substr($product->name . ' | ' . config('app.name'), 0, 60),
                    'en' => mb_substr($product->name . ' | ' . config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $product->description), 0, 160),
                    'en' => mb_substr(strip_tags((string) $product->description), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([$product->name, $product->brand?->name])),
                    'en' => implode(', ', array_filter([$product->name, $product->brand?->name])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/products/' . $product->slug),
                    'en' => url('/en/products/' . $product->slug),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'product'],
                    'en' => ['og:type' => 'product'],
                ],
                'structured_data' => [
                    'lt' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $product->name,
                        'sku' => $product->sku,
                    ],
                    'en' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $product->name,
                        'sku' => $product->sku,
                    ],
                ],
            ]);
        });

        Category::query()->limit(30)->get()->each(function (Category $category) use ($createFor): void {
            $createFor(Category::class, $category->id, [
                'title' => [
                    'lt' => mb_substr($category->name . ' | ' . config('app.name'), 0, 60),
                    'en' => mb_substr($category->name . ' | ' . config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $category->description), 0, 160),
                    'en' => mb_substr(strip_tags((string) $category->description), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([$category->name])),
                    'en' => implode(', ', array_filter([$category->name])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/categories/' . $category->slug),
                    'en' => url('/en/categories/' . $category->slug),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'website'],
                    'en' => ['og:type' => 'website'],
                ],
                'structured_data' => [
                    'lt' => ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $category->name],
                    'en' => ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $category->name],
                ],
            ]);
        });

        Brand::query()->limit(30)->get()->each(function (Brand $brand) use ($createFor): void {
            $createFor(Brand::class, $brand->id, [
                'title' => [
                    'lt' => mb_substr($brand->name . ' | ' . config('app.name'), 0, 60),
                    'en' => mb_substr($brand->name . ' | ' . config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $brand->description), 0, 160),
                    'en' => mb_substr(strip_tags((string) $brand->description), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([$brand->name])),
                    'en' => implode(', ', array_filter([$brand->name])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/brands/' . $brand->slug),
                    'en' => url('/en/brands/' . $brand->slug),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'website'],
                    'en' => ['og:type' => 'website'],
                ],
                'structured_data' => [
                    'lt' => ['@context' => 'https://schema.org', '@type' => 'Brand', 'name' => $brand->name],
                    'en' => ['@context' => 'https://schema.org', '@type' => 'Brand', 'name' => $brand->name],
                ],
            ]);
        });

        $this->command?->info('✅ SEO data seeded.');
    }
}
