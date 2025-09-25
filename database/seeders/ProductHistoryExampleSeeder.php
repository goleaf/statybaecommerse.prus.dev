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
        $catalogManager = $this->createCatalogManager();

        $product = Product::query()->firstWhere('slug', self::PRODUCT_SLUG)
            ?? Product::factory()
                ->for($this->firstOrCreateBrand())
                ->hasAttached(
                    Category::factory()->count(1),
                    [],
                    'categories'
                )
                ->create([
                    'slug' => self::PRODUCT_SLUG,
                    'name' => 'Akrilo hermetikas UE3YIQ',
                    'status' => 'published',
                    'is_visible' => true,
                    'published_at' => Carbon::create(2024, 10, 1, 8, 0, 0),
                ]);

        $this->seedTranslations($product);
        $this->seedProductHistory($product, $catalogManager);
    }

    private function createCatalogManager(): User
    {
        return User::factory()->create([
            'email' => 'catalog.manager@statybae.lt',
            'preferred_locale' => 'lt',
            'name' => 'Catalog Manager',
        ]);
    }

    private function firstOrCreateBrand(): Brand
    {
        return Brand::query()->first()
            ?? Brand::factory()->create([
                'name' => 'StatyBae Premium',
                'is_enabled' => true,
            ]);
    }

    private function seedTranslations(Product $product): void
    {
        ProductTranslation::query()->updateOrCreate(
            ['product_id' => $product->id, 'locale' => 'lt'],
            [
                'name' => $product->name,
                'slug' => self::PRODUCT_SLUG,
                'summary' => 'Profesionalus akrilo hermetikas sandarinimo darbams.',
                'description' => 'Lietuviškas akrilo hermetikas, skirtas profesionaliam langų, durų ir apdailos sandarinimui. Sudaro elastingą, dažomą paviršių.',
                'short_description' => 'Profesionalus akrilo hermetikas, kuris išlieka elastingas.',
                'seo_title' => $product->seo_title ?? 'Akrilo hermetikas UE3YIQ',
                'seo_description' => $product->seo_description ?? 'Aukštos kokybės akrilo hermetikas profesionalams ir meistrams.',
                'meta_keywords' => ['akrilas', 'hermetikas', 'sandarinimas', 'statyba'],
                'alt_text' => 'Akrilo hermetiko tūbelė UE3YIQ',
            ]
        );

        ProductTranslation::query()->updateOrCreate(
            ['product_id' => $product->id, 'locale' => 'en'],
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

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->created()
            ->state([
                'field_name' => 'product',
                'new_value' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => 5.49,
                    'status' => 'published',
                ],
                'description' => 'Initial product import from supplier ERP.',
                'metadata' => ['source' => 'erp_sync', 'channel' => 'b2b_import'] + $baseMetadata,
                'created_at' => Carbon::create(2024, 10, 1, 8, 0),
                'updated_at' => Carbon::create(2024, 10, 1, 8, 0),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->priceChanged()
            ->state([
                'old_value' => 5.49,
                'new_value' => 5.19,
                'description' => 'Autumn promotion applied for seasonal campaign.',
                'metadata' => ['reason' => 'autumn_campaign', 'change_percentage' => -5.46] + $baseMetadata,
                'created_at' => Carbon::create(2024, 10, 15, 9, 30),
                'updated_at' => Carbon::create(2024, 10, 15, 9, 30),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->stockUpdated()
            ->state([
                'old_value' => 150,
                'new_value' => 220,
                'description' => 'Warehouse replenishment processed.',
                'metadata' => ['stock_change' => 70, 'reason' => 'restock', 'supplier' => 'StatyBae Logistics'] + $baseMetadata,
                'created_at' => Carbon::create(2024, 11, 2, 14, 10),
                'updated_at' => Carbon::create(2024, 11, 2, 14, 10),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->updated()
            ->state([
                'field_name' => 'description',
                'old_value' => $product->description,
                'new_value' => $product->description.' Papildytas informacija apie dažomumą.',
                'description' => 'Description enriched with paintability details.',
                'metadata' => ['reason' => 'seo_optimization'] + $baseMetadata,
                'created_at' => Carbon::create(2024, 12, 5, 10, 5),
                'updated_at' => Carbon::create(2024, 12, 5, 10, 5),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->statusChanged()
            ->state([
                'old_value' => 'published',
                'new_value' => 'draft',
                'description' => 'Temporarily disabled due to packaging update.',
                'metadata' => ['reason' => 'packaging_update'] + $baseMetadata,
                'created_at' => Carbon::create(2025, 1, 15, 8, 45),
                'updated_at' => Carbon::create(2025, 1, 15, 8, 45),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->statusChanged()
            ->state([
                'old_value' => 'draft',
                'new_value' => 'published',
                'description' => 'Re-enabled after packaging update approval.',
                'metadata' => ['reason' => 'packaging_approved'] + $baseMetadata,
                'created_at' => Carbon::create(2025, 1, 28, 16, 30),
                'updated_at' => Carbon::create(2025, 1, 28, 16, 30),
            ])
            ->create();

        ProductHistory::factory()
            ->for($product)
            ->for($user)
            ->updated()
            ->state([
                'field_name' => 'is_visible',
                'old_value' => true,
                'new_value' => true,
                'description' => 'Visibility confirmed for new packaging batch.',
                'metadata' => ['reason' => 'quality_assurance'] + $baseMetadata,
                'created_at' => Carbon::create(2025, 1, 28, 16, 35),
                'updated_at' => Carbon::create(2025, 1, 28, 16, 35),
            ])
            ->create();
    }
}
