<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListItemResource;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('feature: mounts the PriceListItemResource index page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this
        ->get(PriceListItemResource::getUrl('index'))
        ->assertOk();
});

it('feature: filters to only show items with a real discount', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $currency = Currency::factory()->create([
        'code' => 'EUR',
    ]);

    $priceListData = [
        'name'        => 'Test Price List',
        'currency_id' => $currency->id,
        'is_enabled'  => true,
        'priority'    => 1,
        'starts_at'   => now()->subDay(),
        'ends_at'     => now()->addDay(),
    ];

    if (Schema::hasColumn('price_lists', 'code')) {
        $priceListData['code'] = 'test-price-list';
    }

    $priceList = PriceList::create($priceListData);

    $discountedProduct = Product::factory()->create([
        'name' => 'Discounted Drill Product',
    ]);

    $fullPriceProduct = Product::factory()->create([
        'name' => 'Full Price Saw Product',
    ]);

    PriceListItem::create([
        'price_list_id'  => $priceList->id,
        'product_id'     => $discountedProduct->id,
        'net_amount'     => 90,
        'compare_amount' => 120,
        'is_active'      => true,
        'valid_from'     => now()->subDay(),
        'valid_until'    => now()->addDay(),
        'name'           => ['en' => 'Discounted Drill'],
    ]);

    PriceListItem::create([
        'price_list_id'  => $priceList->id,
        'product_id'     => $fullPriceProduct->id,
        'net_amount'     => 120,
        'compare_amount' => 120,
        'is_active'      => true,
        'valid_from'     => now()->subDay(),
        'valid_until'    => now()->addDay(),
        'name'           => ['en' => 'Full Price Saw'],
    ]);

    $response = $this->get(PriceListItemResource::getUrl('index') . '?tableFilters[has_discount][isActive]=true');

    $response
        ->assertOk()
        ->assertSee('Discounted Drill Product')
        ->assertDontSee('Full Price Saw Product');
});
