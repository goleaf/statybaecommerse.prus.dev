<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Legal;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;

class ShopperDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Brands
        $brand = Brand::query()->firstOrCreate([
            'slug' => 'acme',
        ], [
            'name' => 'Acme',
            'website' => 'https://example.com',
            'description' => 'Acme brand',
            'is_enabled' => true,
        ]);

        // Categories
        $cat = Category::query()->firstOrCreate([
            'slug' => 'apparel',
        ], [
            'name' => 'Apparel',
            'description' => 'Clothes and accessories',
            'is_enabled' => true,
        ]);

        // Product
        /** @var Product $product */
        $product = Product::query()->firstOrCreate([
            'slug' => 'acme-tshirt',
        ], [
            'name' => 'Acme T-Shirt',
            'brand_id' => $brand->id,
            'type' => 'standard',
            'description' => 'Comfortable cotton t-shirt',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'summary' => 'Soft tee',
            'security_stock' => 5,
        ]);
        $product->categories()->syncWithoutDetaching([$cat->id]);

        // Media: attach placeholder image if present
        $path = 'demo/tshirt.jpg';
        if (Storage::disk('public')->exists($path)) {
            $product
                ->addMedia(Storage::disk('public')->path($path))
                ->toMediaCollection('products');
        }

        // Price in default currency (EUR)
        Price::query()->updateOrCreate([
            'priceable_id' => $product->id,
            'priceable_type' => $product->getMorphClass(),
        ], [
            'amount' => 1999,
            'compare_amount' => 2499,
            'cost_amount' => 1200,
            'currency_id' => \App\Models\Currency::query()->where('code', 'EUR')->value('id') ?? 1,
        ]);

        // Variant
        /** @var ProductVariant $variant */
        $variant = ProductVariant::query()->firstOrCreate([
            'product_id' => $product->id,
            'name' => 'Default',
        ], [
            'sku' => 'ACME-TS-' . Str::upper(Str::random(6)),
            'allow_backorder' => false,
            'status' => 'active',
        ]);

        // Seed variant stock into default location
        $locationId = \App\Models\Location::query()->where('is_default', true)->value('id')
            ?: \App\Models\Location::query()->value('id');
        if ($locationId) {
            \Illuminate\Support\Facades\DB::table('variant_inventories')->upsert([
                [
                    'variant_id' => $variant->id,
                    'location_id' => (int) $locationId,
                    'stock' => 20,
                    'reserved' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['variant_id', 'location_id'], ['stock', 'reserved', 'updated_at']);
        }

        // Legal pages
        $legals = [
            ['slug' => 'privacy', 'title' => 'Privacy Policy'],
            ['slug' => 'terms', 'title' => 'Terms of Use'],
            ['slug' => 'refund', 'title' => 'Refund Policy'],
            ['slug' => 'shipping', 'title' => 'Shipping Policy'],
        ];
        foreach ($legals as $row) {
            Legal::query()->firstOrCreate([
                'slug' => $row['slug'],
            ], [
                'title' => $row['title'],
                'content' => 'Demo ' . strtolower($row['title']) . ' content',
                'is_enabled' => true,
            ]);
        }
    }
}
