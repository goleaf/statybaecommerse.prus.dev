<?php

declare(strict_types=1);

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

        $getLocalized = static function (mixed $value, string $locale, mixed $fallback = null) {
            if (is_string($value)) {
                return $value;
            }
            if (is_array($value)) {
                return $value[$locale]
                    ?? $value['lt']
                    ?? $value['en']
                    ?? (is_array($value) && count($value) ? reset($value) : $fallback);
            }
            return $fallback;
        };

        $getModelField = static function ($model, string $field, string $locale) use ($getLocalized): ?string {
            if (method_exists($model, 'trans')) {
                $val = $model->trans($field, $locale);
                if (!empty($val)) {
                    return is_array($val) ? $getLocalized($val, $locale) : (string) $val;
                }
            }
            $raw = $model->{$field} ?? null;
            return $getLocalized($raw, $locale, is_string($raw) ? $raw : null);
        };

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

        Product::query()->limit(50)->get()->each(function (Product $product) use ($createFor, $getModelField): void {
            $createFor(Product::class, $product->id, [
                'title' => [
                    'lt' => mb_substr((string) $getModelField($product, 'name', 'lt').' | '.config('app.name'), 0, 60),
                    'en' => mb_substr((string) $getModelField($product, 'name', 'en').' | '.config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $getModelField($product, 'description', 'lt')), 0, 160),
                    'en' => mb_substr(strip_tags((string) $getModelField($product, 'description', 'en')), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([(string) $getModelField($product, 'name', 'lt'), $product->brand?->name])),
                    'en' => implode(', ', array_filter([(string) $getModelField($product, 'name', 'en'), $product->brand?->name])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/products/'.($getModelField($product, 'slug', 'lt') ?? $product->id)),
                    'en' => url('/en/products/'.($getModelField($product, 'slug', 'en') ?? $product->id)),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'product'],
                    'en' => ['og:type' => 'product'],
                ],
                'structured_data' => [
                    'lt' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $getModelField($product, 'name', 'lt'),
                        'sku' => $product->sku,
                    ],
                    'en' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $getModelField($product, 'name', 'en'),
                        'sku' => $product->sku,
                    ],
                ],
            ]);
        });

        Category::query()->limit(30)->get()->each(function (Category $category) use ($createFor, $getModelField): void {
            $createFor(Category::class, $category->id, [
                'title' => [
                    'lt' => mb_substr((string) $getModelField($category, 'name', 'lt').' | '.config('app.name'), 0, 60),
                    'en' => mb_substr((string) $getModelField($category, 'name', 'en').' | '.config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $getModelField($category, 'description', 'lt')), 0, 160),
                    'en' => mb_substr(strip_tags((string) $getModelField($category, 'description', 'en')), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([(string) $getModelField($category, 'name', 'lt')])),
                    'en' => implode(', ', array_filter([(string) $getModelField($category, 'name', 'en')])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/categories/'.($getModelField($category, 'slug', 'lt') ?? $category->id)),
                    'en' => url('/en/categories/'.($getModelField($category, 'slug', 'en') ?? $category->id)),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'website'],
                    'en' => ['og:type' => 'website'],
                ],
                'structured_data' => [
                    'lt' => ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $getModelField($category, 'name', 'lt')],
                    'en' => ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $getModelField($category, 'name', 'en')],
                ],
            ]);
        });

        Brand::query()->limit(30)->get()->each(function (Brand $brand) use ($createFor, $getModelField): void {
            $createFor(Brand::class, $brand->id, [
                'title' => [
                    'lt' => mb_substr((string) $getModelField($brand, 'name', 'lt').' | '.config('app.name'), 0, 60),
                    'en' => mb_substr((string) $getModelField($brand, 'name', 'en').' | '.config('app.name'), 0, 60),
                ],
                'description' => [
                    'lt' => mb_substr(strip_tags((string) $getModelField($brand, 'description', 'lt')), 0, 160),
                    'en' => mb_substr(strip_tags((string) $getModelField($brand, 'description', 'en')), 0, 160),
                ],
                'keywords' => [
                    'lt' => implode(', ', array_filter([(string) $getModelField($brand, 'name', 'lt')])),
                    'en' => implode(', ', array_filter([(string) $getModelField($brand, 'name', 'en')])),
                ],
                'canonical_url' => [
                    'lt' => url('/lt/brands/'.($getModelField($brand, 'slug', 'lt') ?? $brand->id)),
                    'en' => url('/en/brands/'.($getModelField($brand, 'slug', 'en') ?? $brand->id)),
                ],
                'meta_tags' => [
                    'lt' => ['og:type' => 'website'],
                    'en' => ['og:type' => 'website'],
                ],
                'structured_data' => [
                    'lt' => ['@context' => 'https://schema.org', '@type' => 'Brand', 'name' => $getModelField($brand, 'name', 'lt')],
                    'en' => ['@context' => 'https://schema.org', '@type' => 'Brand', 'name' => $getModelField($brand, 'name', 'en')],
                ],
            ]);
        });

        $this->command?->info('✅ SEO data seeded.');
    }
}
