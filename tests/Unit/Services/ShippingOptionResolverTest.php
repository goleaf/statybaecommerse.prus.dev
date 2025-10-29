<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Country;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    if (Schema::hasTable('cart_items')) {
        return;
    }

    Schema::create('cart_items', static function (Blueprint $table): void {
        $table->id();
        $table->string('session_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('variant_id')->nullable();
        $table->unsignedBigInteger('product_variant_id')->nullable();
        $table->unsignedInteger('quantity')->default(1);
        $table->unsignedInteger('minimum_quantity')->default(1);
        $table->decimal('unit_price', 12, 2)->default(0);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('price', 12, 2)->nullable();
        $table->decimal('total_price', 12, 2)->default(0);
        $table->json('product_snapshot')->nullable();
        $table->json('attributes')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
});

it('filters shipping options by geography and weight constraints', function (): void {
    // Bootstrap a predictable session identifier for the resolver to locate cart items.
    $sessionId = 'resolver-session-' . Str::uuid();
    Session::setId($sessionId);
    Session::start();

    $resolver = app(ShippingOptionResolver::class);

    $lithuania = Country::query()->updateOrCreate(
        ['cca2' => 'LT'],
        [
            // Keep country metadata predictable so resolver filtering stays deterministic across seeded runs.
            'name'            => 'Lithuania',
            'name_official'   => 'Republic of Lithuania',
            'code'            => 'LT',
            'cca3'            => 'LTU',
            'currency_code'   => 'EUR',
            'currency_symbol' => '€',
            'is_active'       => true,
            'is_enabled'      => true,
            'is_eu_member'    => true,
            'requires_vat'    => true,
            'region'          => 'Europe',
            'subregion'       => 'Northern Europe',
            'timezones'       => ['Europe/Vilnius'],
            'timezone'        => 'Europe/Vilnius',
            'languages'       => ['lt' => 'Lithuanian'],
            'currencies'      => ['EUR' => 'Euro'],
            'vat_rate'        => 21.0,
            'sort_order'      => 1,
        ],
    );

    $estonia = Country::query()->updateOrCreate(
        ['cca2' => 'EE'],
        [
            // Mirror deterministic fixtures for Estonia so unique constraints remain satisfied when seeds pre-populate records.
            'name'            => 'Estonia',
            'name_official'   => 'Republic of Estonia',
            'code'            => 'EE',
            'cca3'            => 'EST',
            'currency_code'   => 'EUR',
            'currency_symbol' => '€',
            'is_active'       => true,
            'is_enabled'      => true,
            'is_eu_member'    => true,
            'requires_vat'    => true,
            'region'          => 'Europe',
            'subregion'       => 'Northern Europe',
            'timezones'       => ['Europe/Tallinn'],
            'timezone'        => 'Europe/Tallinn',
            'languages'       => ['et' => 'Estonian'],
            'currencies'      => ['EUR' => 'Euro'],
            'vat_rate'        => 20.0,
            'sort_order'      => 2,
        ],
    );

    $product = Product::factory()->create([
        'weight' => 4.0,
        'price'  => 80,
    ]);

    $cartItem = CartItem::factory()->guest()->forSession($sessionId)->create([
        'product_id'       => $product->getKey(),
        'price'            => 80,
        'unit_price'       => 80,
        'quantity'         => 1,
        'total_price'      => 80,
        'product_snapshot' => [
            'name'   => $product->name,
            'sku'    => $product->sku,
            'price'  => 80,
            'weight' => 4.0,
        ],
    ]);

    $eligible = ShippingOption::factory()->create([
        'country_id'       => $lithuania->getKey(),
        'min_weight'       => 0,
        'max_weight'       => 10,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 7.50,
        'is_enabled'       => true,
    ]);

    $ineligibleByWeight = ShippingOption::factory()->create([
        'country_id'       => $lithuania->getKey(),
        'min_weight'       => 5,
        'max_weight'       => 15,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 5.00,
        'is_enabled'       => true,
    ]);

    $foreign = ShippingOption::factory()->create([
        'country_id'       => $estonia->getKey(),
        'min_weight'       => 0,
        'max_weight'       => 10,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 6.00,
        'is_enabled'       => true,
    ]);

    $options = $resolver->resolve(collect([$cartItem]), 'LT');

    expect($options->pluck('id'))->toContain($eligible->getKey());
    expect($options->pluck('id'))->not->toContain($ineligibleByWeight->getKey());
    expect($options->pluck('id'))->not->toContain($foreign->getKey());
    expect($options->pluck('price')->map(fn ($price) => (float) $price)->first())->toBe((float) $eligible->price);
});
