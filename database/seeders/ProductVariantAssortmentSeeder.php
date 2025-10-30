<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ProductVariantAssortmentSeeder extends Seeder
{
    /**
     * Seed every visible product with a consistent assortment of colour, size, and pack variants.
     */
    public function run(): void
    {
        // Prepare the attribute catalogue and warehouse records before mutating any product variants.
        $catalogue = $this->ensureAttributeCatalogue();
        $mainLocation = $this->resolveMainLocation();

        if ($catalogue['pack_size'] === null || $mainLocation === null) {
            // Bail out gracefully when the supporting metadata could not be prepared.
            return;
        }

        Product::query()
            ->where('is_visible', true)
            ->with(['variants'])
            ->chunkById(100, function (Collection $products) use ($catalogue, $mainLocation): void {
                /** @var Product $product */
                foreach ($products as $product) {
                    $this->seedAssortmentForProduct($product, $catalogue, $mainLocation);
                }
            });
    }

    /**
     * Ensure colour, size, and pack-size attributes exist with deterministic value lists.
     *
     * @return array{color: ?Attribute, size: ?Attribute, pack_size: ?Attribute}
     */
    private function ensureAttributeCatalogue(): array
    {
        // Reuse existing colour and size attributes when available to avoid duplicating filters.
        $colorAttribute = Attribute::query()->firstWhere('slug', 'color');
        $sizeAttribute = Attribute::query()->firstWhere('slug', 'size');

        // Create or update the dedicated pack-size attribute expected by the storefront matrix.
        $packAttribute = Attribute::query()->updateOrCreate(
            ['slug' => 'pack-size'],
            [
                'name'          => 'Pack Size',
                'type'          => 'select',
                'is_required'   => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled'    => true,
                'sort_order'    => 15,
            ],
        );

        $packValues = [
            ['value' => 'Single Pack', 'order' => 1],
            ['value' => 'Twin Pack', 'order' => 2],
            ['value' => 'Family Pack', 'order' => 3],
            ['value' => 'Bulk Pack', 'order' => 4],
        ];

        foreach ($packValues as $definition) {
            // Keep pack sizes idempotent so repeated seed runs simply refresh labels and ordering.
            AttributeValue::query()->updateOrCreate(
                [
                    'attribute_id' => $packAttribute->getKey(),
                    'slug'         => Str::slug($definition['value']),
                ],
                [
                    'value'         => $definition['value'],
                    'display_value' => $definition['value'],
                    'sort_order'    => $definition['order'],
                    'is_enabled'    => true,
                    'is_active'     => true,
                ],
            );
        }

        return [
            'color'     => $colorAttribute?->loadMissing('values'),
            'size'      => $sizeAttribute?->loadMissing('values'),
            'pack_size' => $packAttribute->loadMissing('values'),
        ];
    }

    /**
     * Ensure a deterministic MAIN warehouse exists for variant inventory assignments.
     */
    private function resolveMainLocation(): ?Location
    {
        // Honour an existing MAIN warehouse when present to respect upstream fixture definitions.
        $location = Location::query()->firstWhere('code', 'MAIN');

        if ($location !== null) {
            return $location;
        }

        return Location::query()->create([
            'code'        => 'MAIN',
            'name'        => 'Main Warehouse',
            'slug'        => 'main-warehouse',
            'is_default'  => true,
            'is_enabled'  => true,
            'country_code'=> 'LT',
        ]);
    }

    /**
     * Create or update a comprehensive variant assortment for the provided product.
     *
     * @param array{color:?Attribute,size:?Attribute,pack_size:Attribute} $catalogue
     */
    private function seedAssortmentForProduct(Product $product, array $catalogue, Location $mainLocation): void
    {
        $colorValues = $catalogue['color']?->values?->take(2) ?? collect();
        $sizeValues = $catalogue['size']?->values?->take(3) ?? collect();
        $packValues = $catalogue['pack_size']->values->sortBy('sort_order')->values()->take(3);

        if ($packValues->isEmpty()) {
            // Without pack dimensions the matrix would collapse to existing seeders, so skip safely.
            return;
        }

        $basePrice = max(9.0, (float) ($product->price ?? 29.0));
        $existingDefault = $product->variants->firstWhere('is_default', true)?->getKey();
        $defaultAssigned = $existingDefault !== null;

        foreach ($packValues as $packIndex => $packValue) {
            foreach ($sizeValues as $sizeIndex => $sizeValue) {
                foreach ($colorValues as $colorIndex => $colorValue) {
                    $variant = $this->createVariantCombination(
                        product: $product,
                        colorAttribute: $catalogue['color'],
                        colorValue: $colorValue,
                        sizeAttribute: $catalogue['size'],
                        sizeValue: $sizeValue,
                        packAttribute: $catalogue['pack_size'],
                        packValue: $packValue,
                        basePrice: $basePrice,
                        packIndex: $packIndex,
                        sizeIndex: $sizeIndex,
                        colorIndex: $colorIndex,
                        mainLocation: $mainLocation,
                    );

                    if (! $defaultAssigned && $variant instanceof ProductVariant) {
                        // Promote the first newly generated variant to default when the product had none.
                        $variant->forceFill(['is_default' => true])->save();
                        $defaultAssigned = true;
                    }
                }
            }
        }
    }

    /**
     * Persist the variant, matrix metadata, and warehouse inventory for a specific combination.
     */
    private function createVariantCombination(
        Product $product,
        ?Attribute $colorAttribute,
        ?AttributeValue $colorValue,
        ?Attribute $sizeAttribute,
        ?AttributeValue $sizeValue,
        Attribute $packAttribute,
        AttributeValue $packValue,
        float $basePrice,
        int $packIndex,
        int $sizeIndex,
        int $colorIndex,
        Location $mainLocation,
    ): ?ProductVariant {
        $combinationKey = implode('|', [
            $product->getKey(),
            $colorValue?->getKey() ?? 'none',
            $sizeValue?->getKey() ?? 'none',
            $packValue->getKey(),
        ]);

        $hash = hash('sha256', $combinationKey);

        $sizeModifier = match (Str::upper((string) ($sizeValue?->value ?? ''))) {
            'XS' => -2.0,
            'S'  => -1.0,
            'L'  => 2.0,
            'XL' => 3.0,
            'XXL' => 4.0,
            default => 0.0,
        };

        $packMultiplier = 1 + ($packIndex * 0.35);
        $colorAdjustment = $colorIndex * 0.5;

        $price = round(max(5.0, ($basePrice * $packMultiplier) + $sizeModifier + $colorAdjustment), 2);
        $compare = round($price * 1.15, 2);
        $cost = round(max(3.0, $price * 0.55), 2);

        $skuParts = [
            Str::slug((string) ($product->slug ?: $product->name)),
            $colorValue?->slug ?? 'nocolor',
            $sizeValue?->slug ?? 'nosize',
            $packValue->slug,
        ];

        $variant = ProductVariant::query()->updateOrCreate(
            [
                'product_id'               => $product->getKey(),
                'variant_combination_hash' => $hash,
            ],
            [
                'name'           => sprintf('%s - %s / %s / %s', $product->name, $packValue->value, $sizeValue?->value ?? 'One Size', $colorValue?->value ?? 'Neutral'),
                'sku'            => Str::upper(implode('-', $skuParts)),
                'price'          => $price,
                'compare_price'  => $compare,
                'cost_price'     => $cost,
                'stock_quantity' => $this->resolveStockForIndexes($packIndex, $sizeIndex, $colorIndex),
                'track_inventory'=> true,
                'is_enabled'     => true,
                'attributes'     => [
                    'pack_size' => $packValue->value,
                    'size'      => $sizeValue?->value,
                    'color'     => $colorValue?->value,
                ],
            ],
        );

        $matrix = [];

        if ($colorAttribute && $colorValue) {
            $matrix['attribute_' . $colorAttribute->getKey()] = $colorValue->getKey();
        }

        if ($sizeAttribute && $sizeValue) {
            $matrix['attribute_' . $sizeAttribute->getKey()] = $sizeValue->getKey();
        }

        $matrix['attribute_' . $packAttribute->getKey()] = $packValue->getKey();

        // Persist the matrix on the variant and synchronise the denormalised attribute tables.
        $variant->forceFill(['variant_attribute_matrix' => $matrix])->save();
        ProductVariantAttributeMatrixService::sync($variant->fresh(), $matrix);

        $this->syncInventory($variant, $mainLocation, $packIndex, $sizeIndex, $colorIndex);

        return $variant;
    }

    /**
     * Persist deterministic inventory figures so storefront availability indicators remain meaningful.
     */
    private function syncInventory(ProductVariant $variant, Location $location, int $packIndex, int $sizeIndex, int $colorIndex): void
    {
        $stock = $this->resolveStockForIndexes($packIndex, $sizeIndex, $colorIndex);
        $reserved = min((int) floor($stock * 0.2), 8);
        $available = max(0, $stock - $reserved);

        VariantInventory::query()->updateOrCreate(
            [
                'variant_id'     => $variant->getKey(),
                'warehouse_code' => $location->code,
            ],
            [
                'location_id'      => $location->getKey(),
                'stock'            => $stock,
                'reserved'         => $reserved,
                'available'        => $available,
                'reorder_point'    => 6 + ($packIndex * 2),
                'reorder_quantity' => 12 + ($sizeIndex * 3),
                'is_tracked'       => true,
                'status'           => 'active',
            ],
        );
    }

    /**
     * Calculate a repeatable stock figure based on the variant's position in the matrix.
     */
    private function resolveStockForIndexes(int $packIndex, int $sizeIndex, int $colorIndex): int
    {
        $base = 48 - ($packIndex * 6) - ($sizeIndex * 4) - ($colorIndex * 3);

        return max(8, $base);
    }
}
