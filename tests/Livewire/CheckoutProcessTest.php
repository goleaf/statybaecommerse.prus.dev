<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;
use App\Livewire\Pages\CheckoutProcess;
use App\Mail\OrderConfirmationMail;
use App\Models\CartItem;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\Zone;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);

it('allows guests to complete a multi-step checkout with dynamic shipping and payment selection', function (): void {
    // Emulate a guest session so cart items can be discovered by the component.
    $sessionId = 'guest-session-' . Str::uuid();
    Session::setId($sessionId);
    Session::start();

    Mail::fake();

    // Prepare a Lithuanian country record to match the checkout defaults and shipping filters.
    $country = Country::query()->firstOrCreate(
        ['code' => 'LT'],
        [
            'name'          => 'Lithuania',
            'cca2'          => 'LT',
            'cca3'          => 'LTU',
            'currency_code' => 'EUR',
            'is_active'     => true,
            'is_enabled'    => true,
        ]
    );

    // Seed a simple product so the cart has a meaningful subtotal and weight.
    $product = Product::factory()->create([
        'weight' => 1.5,
        'price'  => 120,
    ]);

    $zone = Zone::query()->firstOrCreate(
        ['code' => 'LT-ZONE'],
        ['name' => 'Lithuania Zone', 'is_enabled' => true]
    );

    $shippingOption = ShippingOption::query()->create([
        'name'               => 'Courier LT',
        'slug'               => 'courier-lt-' . Str::random(6),
        'carrier_name'       => 'Courier',
        'service_type'       => 'Standard',
        'price'              => 9.99,
        'currency_code'      => 'EUR',
        'country_id'         => $country->getKey(),
        'zone_id'            => $zone->getKey(),
        'is_enabled'         => true,
        'min_weight'         => 0,
        'max_weight'         => 10,
        'min_order_amount'   => 0,
        'max_order_amount'   => null,
        'estimated_days_min' => 1,
        'estimated_days_max' => 3,
        'shipping_matrix'    => [],
    ]);

    Route::get('/__tests/order-confirmation/{number}', fn () => 'ok')->name('order.confirmation');

    $component = Livewire::test(CheckoutProcess::class);
    $activeSessionId = Session::getId();

    CartItem::factory()->guest()->forSession($activeSessionId)->create([
        'product_id'       => $product->getKey(),
        'price'            => 120,
        'unit_price'       => 120,
        'quantity'         => 2,
        'total_price'      => 240,
        'product_snapshot' => [
            'name'   => $product->name,
            'sku'    => $product->sku,
            'price'  => 120,
            'weight' => 1.5,
        ],
    ]);
    $component->call('$refresh');

    // Populate billing data for the first step of the wizard.
    $component
        ->set('billingFirstName', 'Jane')
        ->set('billingLastName', 'Doe')
        ->set('billingEmail', 'jane@example.test')
        ->set('billingPhone', '+3706000000')
        ->set('billingAddress', 'Gedimino pr. 1')
        ->set('billingCity', 'Vilnius')
        ->set('billingPostalCode', '01103')
        ->set('billingCountryCode', 'LT');

    $component->call('nextStep');

    // Confirm that eligible shipping options are surfaced for the second step.
    expect(collect($component->get('availableShippingOptions'))->pluck('id')->all())
        ->toContain($shippingOption->getKey());
    $component->set('selectedShippingOption', $shippingOption->getKey());
    $component->call('nextStep');

    // Select a supported payment method before placing the order.
    $component->set('selectedPaymentMethod', PaymentMethod::CASH_ON_DELIVERY->value);
    $component->call('placeOrder');
    $component->assertHasNoErrors();

    $order = Order::query()->latest('id')->first();
    expect($order)->not->toBeNull();
    expect($order->shipping_option_id)->toBe($shippingOption->getKey());
    expect($order->payment_method)->toBe(PaymentMethod::CASH_ON_DELIVERY);
    expect((float) $order->shipping_amount)->toBe((float) $shippingOption->price);

    Mail::assertQueued(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order): bool {
        // Ensure the queued mail references the newly created order.
        return $mail->order->is($order);
    });
});

it('captures translated country information when billing and shipping differ', function (): void {
    // Start a predictable guest session to align cart discovery with the component lifecycle.
    $sessionId = 'guest-session-' . Str::uuid();
    Session::setId($sessionId);
    Session::start();

    Mail::fake();

    // Provision Baltic country records so both billing and shipping addresses resolve friendly names.
    $lithuania = Country::query()->firstOrCreate(
        ['code' => 'LT'],
        [
            'name'          => 'Lithuania',
            'cca2'          => 'LT',
            'cca3'          => 'LTU',
            'currency_code' => 'EUR',
            'is_active'     => true,
            'is_enabled'    => true,
        ]
    );

    $estonia = Country::query()->firstOrCreate(
        ['code' => 'EE'],
        [
            'name'          => 'Estonia',
            'cca2'          => 'EE',
            'cca3'          => 'EST',
            'currency_code' => 'EUR',
            'is_active'     => true,
            'is_enabled'    => true,
        ]
    );

    $product = Product::factory()->create([
        'weight' => 1.0,
        'price'  => 50,
    ]);

    $shippingOption = ShippingOption::factory()->create([
        'country_id'       => $lithuania->getKey(),
        'min_weight'       => 0,
        'max_weight'       => 10,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 6.99,
        'is_enabled'       => true,
    ]);

    Route::get('/__tests/order-confirmation/{number}', fn () => 'ok')->name('order.confirmation');

    $component = Livewire::test(CheckoutProcess::class);
    $activeSessionId = Session::getId();

    CartItem::factory()->guest()->forSession($activeSessionId)->create([
        'product_id'       => $product->getKey(),
        'price'            => 50,
        'unit_price'       => 50,
        'quantity'         => 1,
        'total_price'      => 50,
        'product_snapshot' => [
            'name'   => $product->name,
            'sku'    => $product->sku,
            'price'  => 50,
            'weight' => 1.0,
        ],
    ]);
    $component->call('$refresh');

    // Complete the billing step while pointing to Estonia and disable the shipping shortcut to provide a Lithuanian delivery.
    $component
        ->set('billingFirstName', 'Ieva')
        ->set('billingLastName', 'Jonaitė')
        ->set('billingEmail', 'ieva@example.test')
        ->set('billingPhone', '+3706000001')
        ->set('billingAddress', 'Laisvės al. 1')
        ->set('billingCity', 'Kaunas')
        ->set('billingPostalCode', '44001')
        ->set('billingCountryCode', 'EE')
        ->set('sameAsShipping', false)
        ->set('shippingFirstName', 'Jonas')
        ->set('shippingLastName', 'Jonaitis')
        ->set('shippingAddress', 'Konstitucijos pr. 7')
        ->set('shippingCity', 'Vilnius')
        ->set('shippingPostalCode', '09308')
        ->set('shippingCountryCode', 'LT');

    $component->call('nextStep');
    expect(collect($component->get('availableShippingOptions'))->pluck('id')->all())
        ->toContain($shippingOption->getKey());
    $component->set('selectedShippingOption', $shippingOption->getKey());
    $component->call('nextStep');

    $component->set('selectedPaymentMethod', PaymentMethod::BANK_TRANSFER->value);
    $component->call('placeOrder');
    $component->assertHasNoErrors();

    $order = Order::query()->latest('id')->first();
    expect($order)->not->toBeNull();
    /** @var array<string, mixed> $billingAddress */
    $billingAddress = (array) $order?->getAttributeValue('billing_address');
    /** @var array<string, mixed> $shippingAddress */
    $shippingAddress = (array) $order?->getAttributeValue('shipping_address');
    $billingTranslation = $order?->getTranslations('billing_address');
    $shippingTranslation = $order?->getTranslations('shipping_address');
    expect(data_get($billingTranslation, 'country'))->toBe('Estonia');
    expect(data_get($billingTranslation, 'country_code'))->toBe('EE');
    expect(data_get($shippingTranslation, 'country'))->toBe('Lithuania');
    expect(data_get($shippingTranslation, 'country_code'))->toBe('LT');
});

it('surfaces and clears shipping option validation when availability changes', function (): void {
    // Keep the session deterministic so cart items map to the active Livewire instance.
    $sessionId = 'guest-session-' . Str::uuid();
    Session::setId($sessionId);
    Session::start();

    Mail::fake();

    $lithuania = Country::query()->firstOrCreate(
        ['code' => 'LT'],
        [
            'name'          => 'Lithuania',
            'cca2'          => 'LT',
            'cca3'          => 'LTU',
            'currency_code' => 'EUR',
            'is_active'     => true,
            'is_enabled'    => true,
        ]
    );

    $latvia = Country::query()->firstOrCreate(
        ['code' => 'LV'],
        [
            'name'          => 'Latvia',
            'cca2'          => 'LV',
            'cca3'          => 'LVA',
            'currency_code' => 'EUR',
            'is_active'     => true,
            'is_enabled'    => true,
        ]
    );

    $product = Product::factory()->create([
        'weight' => 2.0,
        'price'  => 70,
    ]);

    $zone = Zone::query()->firstOrCreate(
        ['code' => 'LT-ZONE'],
        ['name' => 'Lithuania Zone', 'is_enabled' => true]
    );

    $shippingOption = ShippingOption::query()->create([
        'name'               => 'Courier LT Economy',
        'slug'               => 'courier-lt-economy-' . Str::random(6),
        'carrier_name'       => 'Courier',
        'service_type'       => 'Economy',
        'price'              => 8.50,
        'currency_code'      => 'EUR',
        'country_id'         => $lithuania->getKey(),
        'zone_id'            => $zone->getKey(),
        'is_enabled'         => true,
        'min_weight'         => 0,
        'max_weight'         => 5,
        'min_order_amount'   => 0,
        'max_order_amount'   => null,
        'estimated_days_min' => 2,
        'estimated_days_max' => 5,
        'shipping_matrix'    => [],
    ]);

    Route::get('/__tests/order-confirmation/reset/{number}', fn () => 'ok')->name('order.confirmation');

    $component = Livewire::test(CheckoutProcess::class);
    $activeSessionId = Session::getId();

    CartItem::factory()->guest()->forSession($activeSessionId)->create([
        'product_id'       => $product->getKey(),
        'price'            => 70,
        'unit_price'       => 70,
        'quantity'         => 1,
        'total_price'      => 70,
        'product_snapshot' => [
            'name'   => $product->name,
            'sku'    => $product->sku,
            'price'  => 70,
            'weight' => 2.0,
        ],
    ]);
    $component->call('$refresh');

    $component
        ->set('billingFirstName', 'Asta')
        ->set('billingLastName', 'Petrauskienė')
        ->set('billingEmail', 'asta@example.test')
        ->set('billingPhone', '+3706000002')
        ->set('billingAddress', 'Tilto g. 8')
        ->set('billingCity', 'Vilnius')
        ->set('billingPostalCode', '01102')
        ->set('billingCountryCode', 'LT');

    $component->call('nextStep');
    $component->set('selectedShippingOption', $shippingOption->getKey());
    expect($component->get('selectedShippingOption'))->toBe($shippingOption->getKey());
    $component->assertHasNoErrors();

    // Switch to a country with no configured shipping choices and confirm the validation surface is populated.
    $component->set('shippingCountryCode', 'LV');
    $component->assertHasErrors(['selectedShippingOption']);

    // Restore the original country so the resolver repopulates options and removes stale errors.
    $component->set('shippingCountryCode', 'LT');
    $component->assertHasNoErrors();
});
