<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('can complete advanced checkout flow', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
        'stock_quantity' => 10,
        'is_visible' => true,
    ]);

    // Add product to cart first
    session()->put('cart', [
        [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 2,
            'sku' => $product->sku,
        ]
    ]);

    Livewire::test(\App\Livewire\Components\AdvancedCheckout::class)
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '+37060000000')
        ->call('nextStep') // Step 2: Billing
        ->set('billingAddress', '123 Test Street')
        ->set('billingCity', 'Vilnius')
        ->set('billingPostalCode', '01234')
        ->set('billingCountry', 'Lithuania')
        ->call('nextStep') // Step 3: Shipping
        ->call('nextStep') // Step 4: Payment
        ->set('paymentMethod', 'card')
        ->set('agreeToTerms', true)
        ->call('placeOrder');

    // Verify order was created
    expect(\App\Models\Order::count())->toBe(1);
    $order = \App\Models\Order::first();
    expect($order->total)->toBeGreaterThan(0);
    expect($order->items)->toHaveCount(1);
});

it('validates checkout steps properly', function () {
    Livewire::test(\App\Livewire\Components\AdvancedCheckout::class)
        ->set('firstName', '')
        ->call('nextStep')
        ->assertHasErrors(['firstName']);
});

it('can calculate totals correctly', function () {
    $product = Product::factory()->create(['price' => 100.00]);

    session()->put('cart', [
        [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 100.00,
            'quantity' => 2,
        ]
    ]);

    $component = Livewire::test(\App\Livewire\Components\AdvancedCheckout::class);
    
    expect($component->get('subtotal'))->toBe(200.0);
    expect($component->get('taxAmount'))->toBe(42.0); // 21% VAT
    expect($component->get('total'))->toBeGreaterThan(200.0);
});

it('can handle shipping address toggle', function () {
    Livewire::test(\App\Livewire\Components\AdvancedCheckout::class)
        ->set('billingAddress', '123 Test Street')
        ->set('billingCity', 'Vilnius')
        ->set('sameAsBilling', true)
        ->assertSet('shippingAddress', '123 Test Street')
        ->assertSet('shippingCity', 'Vilnius');
});

it('can show customer dashboard with stats', function () {
    $user = User::factory()->create();
    
    // Create some test data
    $order = \App\Models\Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'total' => 150.00,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\CustomerDashboard::class)
        ->assertSee('1') // Total orders
        ->assertSee('€150.00'); // Total spent
});

it('can manage customer wishlist from dashboard', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    $user->wishlist()->attach($product->id);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\CustomerDashboard::class)
        ->call('removeFromWishlist', $product->id);

    expect($user->fresh()->wishlist)->toHaveCount(0);
});

it('can add products to cart from dashboard', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\CustomerDashboard::class)
        ->call('addToCart', $product->id);

    expect(session('cart'))->toHaveCount(1);
});


