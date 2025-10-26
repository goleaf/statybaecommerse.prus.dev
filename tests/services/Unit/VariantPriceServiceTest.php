<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use App\Services\Pricing\VariantPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Ensure the pricing config falls back to the canonical EUR base currency for deterministic conversions.
    config()->set('pricing.currency', 'EUR');
    Currency::factory()->eur()->default()->create();
});

it('calculates base pricing with sale adjustments and variant modifiers', function (): void {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price'               => 100,
        'promotional_price'   => 80,
        'is_on_sale'          => true,
        'sale_start_date'     => now()->subDay(),
        'sale_end_date'       => now()->addDay(),
        'size_price_modifier' => 5,
    ]);

    $service = app(VariantPriceService::class);
    $result = $service->calculate($variant);

    expect($result->salePrice)->toBeFloat()
        ->and($result->salePrice)->toBe(80.0)
        ->and($result->variantModifiers)->toBe(5.0)
        ->and($result->finalPrice)->toBe(85.0);
});

it('applies price list overrides for customer groups', function (): void {
    $customerGroup = CustomerGroup::factory()->active()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price'               => 100,
        'size_price_modifier' => 10,
    ]);

    $priceList = PriceList::factory()->create([
        'currency_id' => Currency::query()->where('code', 'EUR')->value('id'),
        'starts_at'   => now()->subDay(),
        'ends_at'     => now()->addDay(),
        'is_enabled'  => true,
    ]);
    $priceList->customerGroups()->attach($customerGroup);

    PriceListItem::factory()->for($priceList)->create([
        'product_id'   => $product->id,
        'variant_id'   => $variant->id,
        'net_amount'   => 70.0,
        'valid_from'   => now()->subDay(),
        'valid_until'  => now()->addDay(),
        'min_quantity' => 1,
        'max_quantity' => 50,
    ]);

    $service = app(VariantPriceService::class);
    $result = $service->calculate($variant, [
        'customer_group_ids' => [$customerGroup->id],
    ]);

    expect($result->priceListPrice)->toBe(70.0)
        ->and($result->finalPrice)->toBe(70.0)
        ->and($result->variantModifiers)->toBe(0.0);
});

it('applies dynamic pricing rules based on quantity thresholds', function (): void {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 100]);

    VariantPricingRule::factory()->create([
        'product_id'         => $product->id,
        'product_variant_id' => $variant->id,
        'type'               => 'fixed',
        'value'              => -10,
        'min_quantity'       => 5,
        'max_quantity'       => 100,
        'is_active'          => true,
        'valid_from'         => now()->subDay(),
        'valid_until'        => now()->addDay(),
        'is_cumulative'      => false,
    ]);

    $service = app(VariantPriceService::class);
    $result = $service->calculate($variant, ['quantity' => 5]);

    expect($result->dynamicAdjustments)->toBe(-10.0)
        ->and($result->finalPrice)->toBe(90.0);
});

it('converts prices into the requested currency', function (): void {
    Currency::factory()->usd()->create();

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 100]);

    $service = app(VariantPriceService::class);
    $result = $service->calculate($variant, ['currency' => 'USD']);

    expect($result->currency)->toBe('USD')
        ->and($result->finalPrice)->toBeCloseTo(110.0, 0.01);
});

it('records price history entries when requested', function (): void {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price'               => 120,
        'size_price_modifier' => 5,
    ]);

    $service = app(VariantPriceService::class);
    $result = $service->calculate($variant, [
        'record_history'     => true,
        'history_reason'     => 'automated recalculation',
        'history_price_type' => 'regular',
    ]);

    expect($result->historyRecorded)->toBeTrue()
        ->and(VariantPriceHistory::count())->toBe(1);
});
