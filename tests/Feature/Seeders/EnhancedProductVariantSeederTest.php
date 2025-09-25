<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use App\Models\VariantAttributeValue;
use App\Models\VariantInventory;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use App\Models\VariantStockHistory;
use Database\Seeders\EnhancedProductVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates enhanced attributes with proper structure', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify size attribute exists
    $sizeAttribute = Attribute::where('slug', 'size')->first();
    expect($sizeAttribute)->not->toBeNull();
    expect($sizeAttribute->name)->toBe('Size');
    expect($sizeAttribute->type)->toBe('select');
    expect($sizeAttribute->is_required)->toBeTrue();
    expect($sizeAttribute->is_filterable)->toBeTrue();

    // Verify color attribute exists
    $colorAttribute = Attribute::where('slug', 'color')->first();
    expect($colorAttribute)->not->toBeNull();
    expect($colorAttribute->name)->toBe('Color');
    expect($colorAttribute->is_filterable)->toBeTrue();

    // Verify attribute values exist
    expect(AttributeValue::where('attribute_id', $sizeAttribute->id)->count())->toBeGreaterThan(5);
    expect(AttributeValue::where('attribute_id', $colorAttribute->id)->count())->toBeGreaterThan(5);
});

it('creates products with variants using factories', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify products were created
    expect(Product::count())->toBeGreaterThan(0);

    // Verify variants were created
    expect(ProductVariant::count())->toBeGreaterThan(0);

    // Verify products have variants
    $product = Product::with('variants')->first();
    expect($product->variants)->not->toBeEmpty();

    // Verify variant has proper structure
    $variant = $product->variants->first();
    expect($variant->product_id)->toBe($product->id);
    expect($variant->sku)->not->toBeNull();
    expect($variant->price)->toBeGreaterThan(0);
});

it('creates variant inventories using factory relationships', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify variant inventories were created
    expect(VariantInventory::count())->toBeGreaterThan(0);

    // Verify inventories have proper relationships
    $inventory = VariantInventory::with(['variant', 'location'])->first();
    expect($inventory->variant)->not->toBeNull();
    expect($inventory->location)->not->toBeNull();
    expect($inventory->warehouse_code)->not->toBeNull();
    expect($inventory->stock)->toBeGreaterThanOrEqual(0);
});

it('creates variant attribute values with proper relationships', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify variant attribute values were created
    expect(VariantAttributeValue::count())->toBeGreaterThan(0);

    // Verify relationships
    $variantAttributeValue = VariantAttributeValue::with(['variant', 'attribute'])->first();
    expect($variantAttributeValue->variant)->not->toBeNull();
    expect($variantAttributeValue->attribute_name)->not->toBeNull();
    expect($variantAttributeValue->attribute_value)->not->toBeNull();
});

it('creates pricing rules for products', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify pricing rules were created
    expect(VariantPricingRule::count())->toBeGreaterThan(0);

    // Verify pricing rule structure
    $pricingRule = VariantPricingRule::first();
    expect($pricingRule->product_id)->not->toBeNull();
    expect($pricingRule->rule_name)->not->toBeNull();
    expect($pricingRule->rule_type)->not->toBeNull();
    expect($pricingRule->is_active)->toBeTrue();
});

it('creates price history using factories', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify price history was created
    expect(VariantPriceHistory::count())->toBeGreaterThan(0);

    // Verify price history structure
    $priceHistory = VariantPriceHistory::with('variant')->first();
    expect($priceHistory->variant)->not->toBeNull();
    expect($priceHistory->old_price)->toBeGreaterThan(0);
    expect($priceHistory->new_price)->toBeGreaterThan(0);
    expect($priceHistory->price_type)->not->toBeNull();
});

it('creates stock history using factories', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify stock history was created
    expect(VariantStockHistory::count())->toBeGreaterThan(0);

    // Verify stock history structure
    $stockHistory = VariantStockHistory::with('variant')->first();
    expect($stockHistory->variant)->not->toBeNull();
    expect($stockHistory->change_type)->not->toBeNull();
    expect($stockHistory->change_reason)->not->toBeNull();
    expect($stockHistory->quantity_change)->not->toBeNull();
});

it('creates analytics using factories', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify analytics were created
    expect(VariantAnalytics::count())->toBeGreaterThan(0);

    // Verify analytics structure
    $analytics = VariantAnalytics::with('variant')->first();
    expect($analytics->variant)->not->toBeNull();
    expect($analytics->date)->not->toBeNull();
    expect($analytics->views)->toBeGreaterThanOrEqual(0);
    expect($analytics->clicks)->toBeGreaterThanOrEqual(0);
});

it('creates locations when needed for inventory', function () {
    // Ensure no locations exist initially
    Location::query()->delete();

    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify locations were created
    expect(Location::count())->toBeGreaterThan(0);

    // Verify main location exists
    $mainLocation = Location::where('code', 'MAIN')->first();
    expect($mainLocation)->not->toBeNull();
    expect($mainLocation->name)->toBe('Main Warehouse');
});

it('maintains data integrity across all relationships', function () {
    $seeder = new EnhancedProductVariantSeeder;
    $seeder->run();

    // Verify all variants have inventories
    $variantsWithoutInventory = ProductVariant::doesntHave('inventories')->count();
    expect($variantsWithoutInventory)->toBe(0);

    // Verify all variants belong to products
    $variantsWithoutProduct = ProductVariant::whereNull('product_id')->count();
    expect($variantsWithoutProduct)->toBe(0);

    // Verify all products have categories
    $productsWithoutCategories = Product::doesntHave('categories')->count();
    expect($productsWithoutCategories)->toBe(0);
});
