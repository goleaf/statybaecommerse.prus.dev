<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\SingleProduct;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Translations\AttributeValueTranslation;
use App\Models\VariantAttributeValue;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SingleProductVariantSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_selection_updates_pricing_and_stock_context(): void
    {
        // Arrange: create a published variable product so storefront flows render variants.
        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        // Arrange: seed a default variant with comfortable inventory levels for baseline assertions.
        $defaultVariant = ProductVariant::factory()->for($product)->create([
            'price'               => 49.99,
            'is_default_variant'  => true,
            'is_default'          => true,
            'track_inventory'     => true,
            'low_stock_threshold' => 5,
        ]);

        // Arrange: seed a secondary variant that should trigger the low stock message when selected.
        $lowStockVariant = ProductVariant::factory()->for($product)->create([
            'price'               => 89.50,
            'is_default_variant'  => false,
            'is_default'          => false,
            'track_inventory'     => true,
            'low_stock_threshold' => 5,
        ]);

        // Arrange: attach predictable inventory records so available/reserved quantities have deterministic totals.
        VariantInventory::factory()
            ->for($defaultVariant, 'variant')
            ->state([
                'stock'         => 20,
                'reserved'      => 2,
                'available'     => 18,
                'reorder_point' => 5,
            ])
            ->create();

        VariantInventory::factory()
            ->for($lowStockVariant, 'variant')
            ->state([
                'stock'         => 4,
                'reserved'      => 2,
                'available'     => 2,
                'reorder_point' => 5,
            ])
            ->create();

        // Act: mount the Livewire page component through the testing harness.
        $component = Livewire::test(SingleProduct::class, ['product' => $product]);

        // Assert: the default variant is active with pricing and stock summaries reflecting seeded data.
        $this->assertSame($defaultVariant->id, $component->get('activeVariantId'));
        $this->assertSame([
            'reserved'  => 2,
            'available' => 18,
        ], $component->get('inventorySummary'));
        $this->assertEquals((float) $defaultVariant->price, $component->get('pricingSummary')['current']);
        $this->assertSame(
            __('product.variants.messages.in_stock', ['quantity' => 18]),
            $component->get('stockMessage')
        );

        // Act: switch to the low stock variant via the same event the storefront dispatches.
        $component->dispatch('variant.selected', variantId: $lowStockVariant->id);

        // Assert: selecting a different variant updates pricing, inventory, and messaging context together.
        $this->assertSame($lowStockVariant->id, $component->get('activeVariantId'));
        $this->assertSame([
            'reserved'  => 2,
            'available' => 2,
        ], $component->get('inventorySummary'));
        $this->assertEquals((float) $lowStockVariant->price, $component->get('pricingSummary')['current']);
        $this->assertSame(
            __('product.variants.messages.low_stock', ['quantity' => 2]),
            $component->get('stockMessage')
        );
    }

    public function test_variant_attribute_labels_fallback_to_locale_translations_for_known_slugs(): void
    {
        app()->setLocale('lt');

        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => true,
            'is_default'         => true,
            'track_inventory'    => false,
        ]);

        $attribute = Attribute::factory()->create([
            'name' => 'Size Type',
            'slug' => 'size_type',
            'type' => 'select',
        ]);

        VariantAttributeValue::factory()->create([
            'variant_id'              => $variant->id,
            'attribute_id'            => $attribute->id,
            'attribute_name'          => 'Size Type',
            'attribute_value'         => '2',
            'attribute_value_display' => '2',
            'attribute_value_lt'      => '2',
            'attribute_value_en'      => '2',
            'attribute_value_slug'    => '2',
            'sort_order'              => 0,
        ]);

        $component = Livewire::test(SingleProduct::class, ['product' => $product]);

        $variantMatrix = $component->get('variantMatrix');

        $this->assertNotEmpty($variantMatrix);
        $this->assertSame(
            __('products.attributes.size_type'),
            $variantMatrix[0]['attributes'][0]['attribute']
        );
    }

    public function test_pack_size_numeric_value_remains_literal_even_when_attribute_value_translation_is_boolean_like(): void
    {
        app()->setLocale('lt');

        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => true,
            'is_default'         => true,
            'track_inventory'    => false,
        ]);

        $attribute = Attribute::factory()->create([
            'name' => 'Pack Size',
            'slug' => 'pack_size',
            'type' => 'select',
        ]);

        $attributeValue = AttributeValue::query()->create([
            'attribute_id'  => $attribute->id,
            'value'         => '1',
            'display_value' => '1',
            'slug'          => '1',
            'is_enabled'    => true,
            'is_active'     => true,
            'is_searchable' => true,
            'sort_order'    => 0,
        ]);

        AttributeValueTranslation::query()->forceCreate([
            'attribute_value_id' => $attributeValue->id,
            'locale'             => 'lt',
            'value'              => 'Taip',
            'description'        => null,
        ]);

        VariantAttributeValue::factory()->create([
            'variant_id'              => $variant->id,
            'attribute_id'            => $attribute->id,
            'attribute_name'          => 'Pack Size',
            'attribute_value'         => '1',
            'attribute_value_display' => '1',
            'attribute_value_lt'      => '1',
            'attribute_value_en'      => '1',
            'attribute_value_slug'    => '1',
            'sort_order'              => 0,
        ]);

        $component = Livewire::test(SingleProduct::class, ['product' => $product]);
        $variantMatrix = collect($component->get('variantMatrix'));
        $firstVariant = $variantMatrix->first();
        $packSizeAttribute = collect($firstVariant['attributes'] ?? [])
            ->first(static function (array $attribute): bool {
                $slug = (string) ($attribute['attribute_slug'] ?? '');
                $label = (string) ($attribute['attribute'] ?? '');

                return in_array($slug, ['pack_size', 'pack-size'], true)
                    || $label === __('products.attributes.pack_size');
            });

        $this->assertIsArray($packSizeAttribute);
        $this->assertSame('1', (string) ($packSizeAttribute['value'] ?? ''));
    }

    public function test_high_cardinality_numeric_group_does_not_block_color_size_selection(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $colorAttribute = Attribute::factory()->create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $sizeAttribute = Attribute::factory()->create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
        ]);

        $sizeTypeAttribute = Attribute::factory()->create([
            'name' => 'Size Type',
            'slug' => 'size_type',
            'type' => 'select',
        ]);

        $redSmall = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => true,
            'is_default'         => true,
            'track_inventory'    => false,
        ]);

        $redMedium = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        $blueSmall = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        $blueMedium = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        foreach ([
            [$redSmall, $colorAttribute, 'Color', 'Red', 'red'],
            [$redSmall, $sizeAttribute, 'Size', 'S', 's'],
            [$redSmall, $sizeTypeAttribute, 'Size Type', '614', '614'],
            [$redMedium, $colorAttribute, 'Color', 'Red', 'red'],
            [$redMedium, $sizeAttribute, 'Size', 'M', 'm'],
            [$redMedium, $sizeTypeAttribute, 'Size Type', '615', '615'],
            [$blueSmall, $colorAttribute, 'Color', 'Blue', 'blue'],
            [$blueSmall, $sizeAttribute, 'Size', 'S', 's'],
            [$blueSmall, $sizeTypeAttribute, 'Size Type', '616', '616'],
            [$blueMedium, $colorAttribute, 'Color', 'Blue', 'blue'],
            [$blueMedium, $sizeAttribute, 'Size', 'M', 'm'],
            [$blueMedium, $sizeTypeAttribute, 'Size Type', '617', '617'],
        ] as [$variant, $attribute, $attributeName, $value, $valueSlug]) {
            VariantAttributeValue::factory()->create([
                'variant_id'              => $variant->id,
                'attribute_id'            => $attribute->id,
                'attribute_name'          => $attributeName,
                'attribute_value'         => $value,
                'attribute_value_display' => $value,
                'attribute_value_lt'      => $value,
                'attribute_value_en'      => $value,
                'attribute_value_slug'    => $valueSlug,
                'sort_order'              => 0,
            ]);
        }

        $component = Livewire::test(SingleProduct::class, ['product' => $product]);
        $groups = collect($component->get('variantOptionGroups'))->keyBy('slug');
        $sizeGroupValues = collect($groups->get('size')['values'] ?? [])->keyBy('key');
        $sizeTypeValues = collect($groups->get('size_type')['values'] ?? [])->keyBy('key');

        $this->assertSame('compact_list', $groups->get('size_type')['presentation'] ?? null);
        $this->assertSame($redSmall->id, $component->get('activeVariantId'));
        $this->assertNotNull($sizeGroupValues->get('s')['primary_variant_id'] ?? null);
        $this->assertNotNull($sizeGroupValues->get('m')['primary_variant_id'] ?? null);
        $this->assertCount(4, $sizeTypeValues);
        $this->assertNotNull($sizeTypeValues->get('614')['primary_variant_id'] ?? null);
        $this->assertNotNull($sizeTypeValues->get('615')['primary_variant_id'] ?? null);
        $this->assertNotNull($sizeTypeValues->get('616')['primary_variant_id'] ?? null);
        $this->assertNotNull($sizeTypeValues->get('617')['primary_variant_id'] ?? null);
        $this->assertNull($groups->get('size_type')['selected_value_key'] ?? null);

        $component->call('selectVariant', $blueSmall->id);

        $updatedGroups = collect($component->get('variantOptionGroups'))->keyBy('slug');
        $updatedSizeValues = collect($updatedGroups->get('size')['values'] ?? [])->keyBy('key');
        $updatedSizeTypeValues = collect($updatedGroups->get('size_type')['values'] ?? [])->keyBy('key');

        $this->assertSame($blueSmall->id, $component->get('activeVariantId'));
        $this->assertNotNull($updatedSizeValues->get('s')['primary_variant_id'] ?? null);
        $this->assertNotNull($updatedSizeValues->get('m')['primary_variant_id'] ?? null);
        $this->assertCount(4, $updatedSizeTypeValues);
        $this->assertNull($updatedGroups->get('size_type')['selected_value_key'] ?? null);
    }

    public function test_unselected_color_or_size_keeps_all_variant_options_visible(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $colorAttribute = Attribute::factory()->create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $sizeAttribute = Attribute::factory()->create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
        ]);

        $redSmall = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => true,
            'is_default'         => true,
            'track_inventory'    => false,
        ]);

        $redMedium = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        $blueSmall = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        $blueMedium = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => false,
        ]);

        foreach ([
            [$redSmall, $colorAttribute, 'Color', 'Red', 'red'],
            [$redSmall, $sizeAttribute, 'Size', 'S', 's'],
            [$redMedium, $colorAttribute, 'Color', 'Red', 'red'],
            [$redMedium, $sizeAttribute, 'Size', 'M', 'm'],
            [$blueSmall, $colorAttribute, 'Color', 'Blue', 'blue'],
            [$blueSmall, $sizeAttribute, 'Size', 'S', 's'],
            [$blueMedium, $colorAttribute, 'Color', 'Blue', 'blue'],
            [$blueMedium, $sizeAttribute, 'Size', 'M', 'm'],
        ] as [$variant, $attribute, $attributeName, $value, $valueSlug]) {
            VariantAttributeValue::factory()->create([
                'variant_id'              => $variant->id,
                'attribute_id'            => $attribute->id,
                'attribute_name'          => $attributeName,
                'attribute_value'         => $value,
                'attribute_value_display' => $value,
                'attribute_value_lt'      => $value,
                'attribute_value_en'      => $value,
                'attribute_value_slug'    => $valueSlug,
                'sort_order'              => 0,
            ]);
        }

        $component = Livewire::test(SingleProduct::class, ['product' => $product]);

        $initialGroups = collect($component->get('variantOptionGroups'))->keyBy('slug');
        $this->assertNull(data_get($initialGroups->get('color'), 'selected_value_key'));
        $this->assertNull(data_get($initialGroups->get('size'), 'selected_value_key'));
        $this->assertCount(2, data_get($initialGroups->get('color'), 'values', []));
        $this->assertCount(2, data_get($initialGroups->get('size'), 'values', []));
        $this->assertCount(4, $component->get('filteredVariantData'));

        $component->call('selectVariantOption', 'color', 'red', $redSmall->id);

        $colorOnlyGroups = collect($component->get('variantOptionGroups'))->keyBy('slug');
        $this->assertSame('red', data_get($colorOnlyGroups->get('color'), 'selected_value_key'));
        $this->assertNull(data_get($colorOnlyGroups->get('size'), 'selected_value_key'));
        $this->assertCount(2, data_get($colorOnlyGroups->get('color'), 'values', []));
        $this->assertCount(2, data_get($colorOnlyGroups->get('size'), 'values', []));
        $this->assertCount(2, $component->get('filteredVariantData'));

        $component->call('selectVariantOption', 'size', 'm', $redMedium->id);
        $this->assertSame($redMedium->id, $component->get('activeVariantId'));
        $this->assertCount(1, $component->get('filteredVariantData'));

        $component->call('clearVariantSelection', 'color');

        $groupsAfterClearingColor = collect($component->get('variantOptionGroups'))->keyBy('slug');
        $this->assertNull(data_get($groupsAfterClearingColor->get('color'), 'selected_value_key'));
        $this->assertCount(2, data_get($groupsAfterClearingColor->get('color'), 'values', []));
        $this->assertCount(2, data_get($groupsAfterClearingColor->get('size'), 'values', []));
        $this->assertCount(2, $component->get('filteredVariantData'));

        $component->call('clearVariantSelection');
        $this->assertCount(4, $component->get('filteredVariantData'));
    }
}
