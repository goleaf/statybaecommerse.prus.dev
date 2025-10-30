<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use App\Models\VariantAttributeValue;
use App\Models\VariantInventory;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use App\Models\VariantStockHistory;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class EnhancedProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        // Prepare the attribute catalogue and stock locations before creating any products.
        $attributes = $this->seedCoreAttributes();
        $locations = $this->ensureLocations();

        // Create a realistic catalogue of products with multiple variants captured for later enrichment.
        $variants = $this->seedProductsWithVariants($attributes);

        // Attach inventory, pricing, historical, and analytics data to every generated variant.
        $this->seedVariantInventories($variants, $locations['main']);
        $this->seedPricingRules($variants);
        $this->seedPriceHistories($variants);
        $this->seedStockHistories($variants);
        $this->seedAnalytics($variants);
    }

    /**
     * Ensure the size and color attributes exist with a healthy catalogue of values.
     *
     * @return array<string, Attribute>
     */
    private function seedCoreAttributes(): array
    {
        // Guarantee the size attribute exists with deterministic properties for filtering scenarios.
        $sizeAttribute = Attribute::query()->firstOrCreate(
            ['slug' => 'size'],
            [
                'name'          => 'Size',
                'type'          => 'select',
                'is_required'   => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled'    => true,
                'sort_order'    => 1,
            ],
        );

        $sizes = [
            ['value' => 'XS', 'label' => 'Extra Small', 'order' => 1],
            ['value' => 'S', 'label' => 'Small', 'order' => 2],
            ['value' => 'M', 'label' => 'Medium', 'order' => 3],
            ['value' => 'L', 'label' => 'Large', 'order' => 4],
            ['value' => 'XL', 'label' => 'Extra Large', 'order' => 5],
            ['value' => 'XXL', 'label' => 'Double Extra Large', 'order' => 6],
            ['value' => 'XXXL', 'label' => 'Triple Extra Large', 'order' => 7],
        ];

        foreach ($sizes as $size) {
            // Keep attribute values idempotent so reruns update instead of duplicating rows.
            AttributeValue::query()->updateOrCreate(
                [
                    'attribute_id' => $sizeAttribute->getKey(),
                    'value'        => $size['value'],
                ],
                [
                    'slug'          => Str::slug($size['value']),
                    'display_value' => $size['label'],
                    'sort_order'    => $size['order'],
                    'is_enabled'    => true,
                ],
            );
        }

        // Guarantee the color attribute mirrors the expectations in the feature tests.
        $colorAttribute = Attribute::query()->firstOrCreate(
            ['slug' => 'color'],
            [
                'name'          => 'Color',
                'type'          => 'select',
                'is_required'   => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled'    => true,
                'sort_order'    => 2,
            ],
        );

        $colors = [
            ['value' => 'Black', 'hex' => '#000000', 'order' => 1],
            ['value' => 'White', 'hex' => '#FFFFFF', 'order' => 2],
            ['value' => 'Red', 'hex' => '#FF0000', 'order' => 3],
            ['value' => 'Blue', 'hex' => '#0000FF', 'order' => 4],
            ['value' => 'Green', 'hex' => '#008000', 'order' => 5],
            ['value' => 'Yellow', 'hex' => '#FFFF00', 'order' => 6],
            ['value' => 'Gray', 'hex' => '#808080', 'order' => 7],
            ['value' => 'Brown', 'hex' => '#A52A2A', 'order' => 8],
        ];

        foreach ($colors as $color) {
            // Persist each color while keeping metadata useful for admin filters.
            AttributeValue::query()->updateOrCreate(
                [
                    'attribute_id' => $colorAttribute->getKey(),
                    'value'        => $color['value'],
                ],
                [
                    'slug'          => Str::slug($color['value']),
                    'display_value' => $color['value'],
                    'hex_color'     => $color['hex'],
                    'sort_order'    => $color['order'],
                    'is_enabled'    => true,
                ],
            );
        }

        // Return hydrated attributes so downstream calls can reference their value collections.
        return [
            'size'  => $sizeAttribute->load('values'),
            'color' => $colorAttribute->load('values'),
        ];
    }

    /**
     * Ensure the inventory infrastructure has a canonical main warehouse.
     *
     * @return array<string, Location>
     */
    private function ensureLocations(): array
    {
        // Reuse an existing MAIN location when present to prevent duplicate warehouses.
        $mainLocation = Location::query()->firstWhere('code', 'MAIN');

        if ($mainLocation === null) {
            // Create a deterministic MAIN warehouse with predictable naming for assertions.
            $mainLocation = Location::factory()
                ->warehouse()
                ->state([
                    'code'        => 'MAIN',
                    'name'        => 'Main Warehouse',
                    'slug'        => 'main-warehouse',
                    'is_default'  => true,
                    'is_enabled'  => true,
                    'country_code'=> 'LT',
                ])
                ->create();
        }

        return ['main' => $mainLocation];
    }

    /**
     * Create products with curated variants and keep the resulting models handy for follow-up seeders.
     *
     * @param array<string, Attribute> $attributes
     * @return EloquentCollection<int, ProductVariant>
     */
    private function seedProductsWithVariants(array $attributes): EloquentCollection
    {
        // Stabilise brand and category lookups so relationships remain valid between runs.
        $brand = Brand::query()->firstOrCreate(
            ['slug' => 'fashion-brand'],
            [
                'name'        => 'Fashion Brand',
                'description' => 'Premium fashion pieces',
                'is_enabled'  => true,
            ],
        );

        $category = Category::query()->firstOrCreate(
            ['slug' => 'clothing'],
            [
                'name'        => 'Clothing',
                'description' => 'All apparel items',
                'is_enabled'  => true,
                'is_visible'  => true,
            ],
        );

        $productsBlueprint = [
            [
                'name'        => 'Premium T-Shirt',
                'description' => 'High-quality cotton t-shirt with modern design',
                'base_price'  => 29.99,
                'variants'    => [
                    ['size' => 'S', 'color' => 'Black', 'price_modifier' => 0.0, 'stock' => 50],
                    ['size' => 'M', 'color' => 'White', 'price_modifier' => 0.0, 'stock' => 60],
                    ['size' => 'L', 'color' => 'Red', 'price_modifier' => 2.5, 'stock' => 45],
                ],
            ],
            [
                'name'        => 'Designer Jeans',
                'description' => 'Premium denim jeans with perfect fit',
                'base_price'  => 89.99,
                'variants'    => [
                    ['size' => 'M', 'color' => 'Blue', 'price_modifier' => 0.0, 'stock' => 40],
                    ['size' => 'L', 'color' => 'Gray', 'price_modifier' => 4.0, 'stock' => 35],
                    ['size' => 'XL', 'color' => 'Black', 'price_modifier' => 6.0, 'stock' => 25],
                ],
            ],
        ];

        $variants = new EloquentCollection();

        foreach ($productsBlueprint as $productData) {
            // Compose an anchor product with predictable pricing metadata.
            $product = Product::factory()
                ->for($brand)
                ->state([
                    'name'              => $productData['name'],
                    'slug'              => Str::slug($productData['name']),
                    'description'       => $productData['description'],
                    'short_description' => Str::limit($productData['description'], 120),
                    'price'             => $productData['base_price'],
                    'compare_price'     => $productData['base_price'] * 1.2,
                    'cost_price'        => $productData['base_price'] * 0.6,
                    'type'              => 'variable',
                    'manage_stock'      => true,
                    'is_visible'        => true,
                    'is_featured'       => true,
                    'published_at'      => now(),
                ])
                ->create();

            // Ensure catalogue integrity by linking the product to its apparel category.
            $product->categories()->syncWithoutDetaching([$category->getKey()]);

            foreach ($productData['variants'] as $index => $variantData) {
                // Persist the variant with tailored pricing and attribute payloads.
                $variant = ProductVariant::factory()
                    ->for($product)
                    ->state([
                        'name'            => sprintf('%s - %s %s', $productData['name'], $variantData['size'], $variantData['color']),
                        'sku'             => strtoupper(Str::slug($productData['name'] . '-' . $variantData['size'] . '-' . $variantData['color'])),
                        'price'           => $productData['base_price'] + $variantData['price_modifier'],
                        'compare_price'   => ($productData['base_price'] + $variantData['price_modifier']) * 1.2,
                        'cost_price'      => ($productData['base_price'] + $variantData['price_modifier']) * 0.6,
                        'stock_quantity'  => $variantData['stock'],
                        'track_inventory' => true,
                        'is_default'      => $index === 0,
                        'is_enabled'      => true,
                        'attributes'      => [
                            'size'  => $variantData['size'],
                            'color' => $variantData['color'],
                        ],
                    ])
                    ->create();

                $variant->setRelation('product', $product);

                // Build the variant attribute matrix so filters and combinations are consistent.
                $matrix = [];
                $sizeValue = $attributes['size']->values->firstWhere('value', $variantData['size']);
                if ($sizeValue !== null) {
                    $matrix['attribute_' . $attributes['size']->getKey()] = $sizeValue->getKey();
                }

                $colorValue = $attributes['color']->values->firstWhere('value', $variantData['color']);
                if ($colorValue !== null) {
                    $matrix['attribute_' . $attributes['color']->getKey()] = $colorValue->getKey();
                }

                if ($matrix !== []) {
                    $variant->forceFill(['variant_attribute_matrix' => $matrix])->save();
                    ProductVariantAttributeMatrixService::sync($variant->fresh(), $matrix);
                }

                // Persist readable variant attribute rows for analytics and storefront consumption.
                $this->createVariantAttributeValue($variant, $attributes['size'], $variantData['size']);
                $this->createVariantAttributeValue($variant, $attributes['color'], $variantData['color']);

                $variants->push($variant);
            }
        }

        return $variants;
    }

    private function createVariantAttributeValue(ProductVariant $variant, Attribute $attribute, string $rawValue): void
    {
        // Normalise values to keep lookups deterministic regardless of casing.
        $attributeValue = $attribute->values->firstWhere(
            fn (AttributeValue $value) => Str::lower($value->value) === Str::lower($rawValue)
        );

        $display = $attributeValue?->display_value ?: $rawValue;

        VariantAttributeValue::query()->updateOrCreate(
            [
                'variant_id'   => $variant->getKey(),
                'attribute_id' => $attribute->getKey(),
                'attribute_value' => $display,
            ],
            [
                'attribute_name'          => $attribute->name,
                'attribute_value_display' => $display,
                'attribute_value_slug'    => Str::slug($display),
                'attribute_value_lt'      => $display,
                'attribute_value_en'      => $display,
                'sort_order'              => $attributeValue?->sort_order ?? 0,
                'is_filterable'           => true,
                'is_searchable'           => true,
            ],
        );
    }

    /**
     * Assign warehouse stock for every variant using the main location.
     */
    private function seedVariantInventories(EloquentCollection $variants, Location $mainLocation): void
    {
        foreach ($variants as $variant) {
            // Keep inventory unique per variant/location pair to satisfy the unique index.
            VariantInventory::query()->updateOrCreate(
                [
                    'variant_id'     => $variant->getKey(),
                    'warehouse_code' => $mainLocation->code,
                ],
                [
                    'location_id'      => $mainLocation->getKey(),
                    'stock'            => max(0, $variant->stock_quantity),
                    'reserved'         => 0,
                    'available'        => max(0, $variant->stock_quantity),
                    'reorder_point'    => 10,
                    'reorder_quantity' => 30,
                    'is_tracked'       => true,
                    'status'           => 'active',
                ],
            );
        }
    }

    /**
     * Create pricing rules per product so variants inherit dynamic pricing metadata.
     */
    private function seedPricingRules(EloquentCollection $variants): void
    {
        $products = $variants
            ->map(fn (ProductVariant $variant) => $variant->getRelation('product') ?? $variant->product)
            ->filter()
            ->unique(fn (?Product $product) => $product?->getKey())
            ->filter();

        foreach ($products as $product) {
            $primaryVariant = $variants->first(fn (ProductVariant $variant) => $variant->product_id === $product->getKey());

            if ($primaryVariant === null) {
                continue;
            }

            // Size-based surcharge rule for larger garments.
            VariantPricingRule::factory()
                ->state([
                    // The pricing rules table only exposes a "name" column, so we reuse it for the
                    // deterministic identifier that feature tests assert against.
                    'product_id'         => $product->getKey(),
                    'product_variant_id' => $primaryVariant->getKey(),
                    'name'               => 'Large Size Premium',
                    'type'               => 'percentage',
                    'value'              => 10,
                    'min_quantity'       => 1,
                    'priority'           => 1,
                    'is_active'          => true,
                    // Capture the original rule intent in the description so the admin UI still
                    // surfaces the context that previously lived in the removed `rule_type` column.
                    'description'        => 'size_based surcharge',
                ])
                ->create();

            // Loyalty discount for bulk purchases to diversify rule coverage.
            VariantPricingRule::factory()
                ->state([
                    // Keep the loyalty discount human readable via the canonical "name" column.
                    'product_id'         => $product->getKey(),
                    'product_variant_id' => $primaryVariant->getKey(),
                    'name'               => 'Loyalty Bulk Discount',
                    'type'               => 'percentage',
                    'value'              => -15,
                    'min_quantity'       => 5,
                    'priority'           => 2,
                    'is_active'          => true,
                    'description'        => 'quantity_based discount',
                ])
                ->create();
        }
    }

    /**
     * Store historical pricing adjustments for analytical regression tests.
     */
    private function seedPriceHistories(EloquentCollection $variants): void
    {
        foreach ($variants as $variant) {
            VariantPriceHistory::factory()
                ->for($variant, 'variant')
                ->state([
                    'old_price' => max(1, $variant->price - 5),
                    'new_price' => $variant->price,
                    'price_type'=> 'regular',
                    'reason'    => 'manual',
                    'change_reason' => 'manual',
                ])
                ->create();
        }
    }

    /**
     * Capture stock history transitions for each variant.
     */
    private function seedStockHistories(EloquentCollection $variants): void
    {
        foreach ($variants as $variant) {
            VariantStockHistory::factory()
                ->for($variant, 'variant')
                ->increase()
                ->state([
                    'old_quantity'    => max(0, $variant->stock_quantity - 5),
                    'new_quantity'    => $variant->stock_quantity,
                    'change_reason'   => 'restock',
                    'reference_type'  => 'order',
                    'reference_id'    => 1,
                ])
                ->create();
        }
    }

    /**
     * Record analytics snapshots so downstream metrics have source data.
     */
    private function seedAnalytics(EloquentCollection $variants): void
    {
        foreach ($variants as $variant) {
            $product = $variant->getRelation('product') ?? $variant->product;

            if ($product === null) {
                continue;
            }

            VariantAnalytics::factory()
                ->for($product, 'product')
                ->for($variant, 'variant')
                ->state([
                    'date'        => now()->toDateString(),
                    'date_bucket' => sprintf('%s:%s', VariantAnalytics::BUCKET_DAILY, now()->toDateString()),
                    'views'       => 150,
                    'clicks'      => 60,
                    'add_to_cart' => 25,
                    'purchases'   => 10,
                    'revenue'     => max(10, $variant->price * 10),
                ])
                ->create();
        }
    }
}
