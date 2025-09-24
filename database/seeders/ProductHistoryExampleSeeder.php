<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\Translations\ProductTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class ProductHistoryExampleSeeder extends Seeder
{
    private const PRODUCT_SLUG = 'akrilo-hermetikas-ue3yiq';

    public function run(): void
    {
        $user = $this->ensureCatalogManager();
        $brand = $this->ensureBrand();
        $category = $this->ensureCategory();
        $product = $this->ensureProduct($brand->id, $category->id);

        $this->seedTranslations($product->id);
        $this->seedProductHistory($product, $user);
    }

    private function ensureCatalogManager(): User
    {
        return User::query()->firstOrCreate(
            ['email' => 'catalog.manager@statybae.lt'],
            [
                'name' => 'Catalog Manager',
                'first_name' => 'Catalog',
                'last_name' => 'Manager',
                'password' => bcrypt('password'),
                'email_verified_at' => Carbon::now(),
                'preferred_locale' => 'lt',
            ]
        );
    }

    private function ensureBrand(): Brand
    {
        return Brand::query()->firstOrCreate(
            ['slug' => 'statybae-premium'],
            [
                'name' => 'StatyBae Premium',
                'description' => 'Premium building chemistry showcased in demo seeding.',
                'website' => 'https://statybaecommerse.prus.dev',
                'is_enabled' => true,
                'sort_order' => 5,
                'seo_title' => 'StatyBae Premium',
                'seo_description' => 'Premium construction chemistry and sealants.',
            ]
        );
    }

    private function ensureCategory(): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'akriliniai-hermetikai'],
            [
                'name' => 'Akriliniai hermetikai',
                'description' => 'Akriliniai hermetikai ir sandarinimo sprendimai statyboms.',
                'sort_order' => 15,
                'is_visible' => true,
                'seo_title' => 'Akriliniai hermetikai',
                'seo_description' => 'Profesionalūs akrilo hermetikų sprendimai StatyBae parduotuvėje.',
            ]
        );
    }

    private function ensureProduct(int $brandId, int $categoryId): Product
    {
        $publishedAt = Carbon::create(2024, 10, 1, 8, 0, 0);

        $product = Product::withoutEvents(function () use ($brandId, $publishedAt) {
            return Product::query()->updateOrCreate(
                ['slug' => self::PRODUCT_SLUG],
                [
                    'name' => 'Akrilo hermetikas UE3YIQ',
                    'description' => 'Profesionalus akrilo hermetikas, skirtas langų, durų ir apdailos sandarinimui. Sudėtyje esančios elastingos dervos užtikrina ilgalaikį rezultatą.',
                    'short_description' => 'Profesionalus akrilo hermetikas vidaus darbams.',
                    'sku' => 'AKR-UE3YIQ',
                    'barcode' => '5901234567890',
                    'price' => 5.49,
                    'sale_price' => 4.99,
                    'compare_price' => 6.49,
                    'cost_price' => 3.10,
                    'manage_stock' => true,
                    'track_stock' => true,
                    'allow_backorder' => false,
                    'stock_quantity' => 150,
                    'low_stock_threshold' => 15,
                    'weight' => 0.32,
                    'length' => 23.5,
                    'width' => 5.1,
                    'height' => 5.1,
                    'is_visible' => true,
                    'is_featured' => true,
                    'is_requestable' => false,
                    'published_at' => $publishedAt,
                    'seo_title' => 'Akrilo hermetikas UE3YIQ',
                    'seo_description' => 'Profesionalus akrilo hermetikas langams ir durims su puikiu sukibimu.',
                    'brand_id' => $brandId,
                    'status' => 'published',
                    'type' => 'simple',
                    'metadata' => [
                        'color' => 'Baltas',
                        'volume_ml' => 280,
                        'application' => 'Sandarinimas vidaus darbams',
                    ],
                ]
            );
        });

        $product->categories()->syncWithoutDetaching([$categoryId]);

        return $product;
    }

    private function seedTranslations(int $productId): void
    {
        ProductTranslation::query()->updateOrCreate(
            ['product_id' => $productId, 'locale' => 'lt'],
            [
                'name' => 'Akrilo hermetikas UE3YIQ',
                'slug' => self::PRODUCT_SLUG,
                'summary' => 'Profesionalus akrilo hermetikas sandarinimo darbams.',
                'description' => 'Lietuviškas akrilo hermetikas, skirtas profesionaliam langų, durų ir apdailos sandarinimui. Sudaro elastingą, dažomą paviršių.',
                'short_description' => 'Profesionalus akrilo hermetikas, kuris išlieka elastingas.',
                'seo_title' => 'Akrilo hermetikas UE3YIQ',
                'seo_description' => 'Aukštos kokybės akrilo hermetikas profesionalams ir meistrams.',
                'meta_keywords' => ['akrilas', 'hermetikas', 'sandarinimas', 'statyba'],
                'alt_text' => 'Akrilo hermetiko tūbelė UE3YIQ',
            ]
        );

        ProductTranslation::query()->updateOrCreate(
            ['product_id' => $productId, 'locale' => 'en'],
            [
                'name' => 'Acrylic Sealant UE3YIQ',
                'slug' => 'acrylic-sealant-ue3yiq',
                'summary' => 'Professional acrylic sealant for joinery and finishing.',
                'description' => 'Flexible acrylic sealant for windows, doors and finishing joints. Forms a paintable, long-lasting seal.',
                'short_description' => 'Flexible acrylic sealant designed for interior sealing jobs.',
                'seo_title' => 'Acrylic Sealant UE3YIQ',
                'seo_description' => 'Reliable acrylic sealant with excellent adhesion and elasticity.',
                'meta_keywords' => ['acrylic sealant', 'interior', 'construction'],
                'alt_text' => 'Acrylic sealant tube UE3YIQ',
            ]
        );
    }

    private function seedProductHistory(Product $product, User $user): void
    {
        $baseMetadata = [
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'brand' => $product->brand?->name,
            'categories' => $product->categories->pluck('name')->toArray(),
        ];

        $timeline = [
            [
                'action' => 'created',
                'field_name' => 'product',
                'old_value' => null,
                'new_value' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => 5.49,
                    'status' => 'published',
                ],
                'description' => 'Initial product import from supplier ERP.',
                'metadata' => ['source' => 'erp_sync', 'channel' => 'b2b_import'],
                'created_at' => Carbon::create(2024, 10, 1, 8, 0),
            ],
            [
                'action' => 'price_changed',
                'field_name' => 'price',
                'old_value' => 5.49,
                'new_value' => 5.19,
                'description' => 'Autumn promotion applied for seasonal campaign.',
                'metadata' => ['reason' => 'autumn_campaign', 'change_percentage' => -5.46],
                'created_at' => Carbon::create(2024, 10, 15, 9, 30),
            ],
            [
                'action' => 'stock_updated',
                'field_name' => 'stock_quantity',
                'old_value' => 150,
                'new_value' => 220,
                'description' => 'Warehouse replenishment processed.',
                'metadata' => ['stock_change' => 70, 'reason' => 'restock', 'supplier' => 'StatyBae Logistics'],
                'created_at' => Carbon::create(2024, 11, 2, 14, 10),
            ],
            [
                'action' => 'updated',
                'field_name' => 'description',
                'old_value' => $product->description,
                'new_value' => $product->description.' Papildytas informacija apie dažomumą.',
                'description' => 'Description enriched with paintability details.',
                'metadata' => ['reason' => 'seo_optimization'],
                'created_at' => Carbon::create(2024, 12, 5, 10, 5),
            ],
            [
                'action' => 'status_changed',
                'field_name' => 'status',
                'old_value' => 'published',
                'new_value' => 'draft',
                'description' => 'Temporarily disabled due to packaging update.',
                'metadata' => ['reason' => 'packaging_update'],
                'created_at' => Carbon::create(2025, 1, 15, 8, 45),
            ],
            [
                'action' => 'status_changed',
                'field_name' => 'status',
                'old_value' => 'draft',
                'new_value' => 'published',
                'description' => 'Re-enabled after packaging update approval.',
                'metadata' => ['reason' => 'packaging_approved'],
                'created_at' => Carbon::create(2025, 1, 28, 16, 30),
            ],
            [
                'action' => 'updated',
                'field_name' => 'is_visible',
                'old_value' => true,
                'new_value' => true,
                'description' => 'Visibility confirmed for new packaging batch.',
                'metadata' => ['reason' => 'quality_assurance'],
                'created_at' => Carbon::create(2025, 1, 28, 16, 35),
            ],
        ];

        foreach ($timeline as $entry) {
            ProductHistory::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'action' => $entry['action'],
                    'field_name' => $entry['field_name'],
                    'created_at' => $entry['created_at'],
                ],
                [
                    'user_id' => $user->id,
                    'old_value' => $entry['old_value'] ?? null,
                    'new_value' => $entry['new_value'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'ip_address' => '192.168.1.10',
                    'user_agent' => 'Seeder/1.0 (+https://statybaecommerse.prus.dev)',
                    'metadata' => array_merge($baseMetadata, $entry['metadata'] ?? []),
                    'causer_type' => User::class,
                    'causer_id' => $user->id,
                    'created_at' => $entry['created_at'],
                    'updated_at' => $entry['created_at'],
                ]
            );
        }
    }
}
