<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Database\Seeders\ProductVariantAssortmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('seeds variant inventory counts at one tenth of the base values', function () {
    $colorAttribute = Attribute::factory()->create([
        'name'       => 'Color',
        'slug'       => 'color',
        'type'       => 'select',
        'is_enabled' => true,
        'is_active'  => true,
        'is_visible' => true,
    ]);

    $sizeAttribute = Attribute::factory()->create([
        'name'       => 'Size',
        'slug'       => 'size',
        'type'       => 'select',
        'is_enabled' => true,
        'is_active'  => true,
        'is_visible' => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id'  => $colorAttribute->getKey(),
        'value'         => 'Red',
        'slug'          => 'red',
        'sort_order'    => 1,
        'display_value' => 'Red',
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id'  => $colorAttribute->getKey(),
        'value'         => 'Blue',
        'slug'          => 'blue',
        'sort_order'    => 2,
        'display_value' => 'Blue',
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id'  => $sizeAttribute->getKey(),
        'value'         => 'S',
        'slug'          => 's',
        'sort_order'    => 1,
        'display_value' => 'S',
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id'  => $sizeAttribute->getKey(),
        'value'         => 'M',
        'slug'          => 'm',
        'sort_order'    => 2,
        'display_value' => 'M',
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    AttributeValue::factory()->create([
        'attribute_id'  => $sizeAttribute->getKey(),
        'value'         => 'L',
        'slug'          => 'l',
        'sort_order'    => 3,
        'display_value' => 'L',
        'is_enabled'    => true,
        'is_active'     => true,
    ]);

    $product = Product::factory()->create([
        'is_visible' => true,
        'price'      => 30.0,
    ]);

    $this->seed(ProductVariantAssortmentSeeder::class);

    $variants = ProductVariant::query()
        ->where('product_id', $product->getKey())
        ->get();

    expect($variants)->not->toBeEmpty();

    $maxVariantStock = $variants->max('stock_quantity');
    expect($maxVariantStock)->toBeLessThanOrEqual(5);

    $maxInventoryStock = VariantInventory::query()
        ->whereIn('variant_id', $variants->pluck('id'))
        ->max('stock');

    expect($maxInventoryStock)->toBeLessThanOrEqual(5);
});
