<?php declare(strict_types=1);

use App\Models\Channel;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\OrderSeeder;

it('seeds at least one order with items', function () {
    // Minimal prerequisites for OrderSeeder
    $user = User::factory()->create();
    $channel = Channel::factory()->create();
    $currency = Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);
    $zone = Zone::factory()->create(['currency_id' => $currency->id, 'is_default' => true]);

    // A few visible, published products
    Product::factory()->count(3)->create([
        'is_visible' => true,
        'published_at' => now(),
    ]);

    // Ensure the seeder has the required data by creating them if they don't exist
    // The seeder looks for existing data, so we need to make sure it exists
    if (!\App\Models\Channel::query()->exists()) {
        Channel::factory()->create();
    }
    if (!\App\Models\Zone::query()->exists()) {
        Zone::factory()->create(['currency_id' => $currency->id]);
    }
    if (!\App\Models\Currency::query()->where('code', 'EUR')->exists()) {
        Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);
    }
    
    // The orders table has a foreign key constraint to sh_zones table, not zones table
    // We need to create a zone in the sh_zones table for the foreign key constraint
    if (!\Illuminate\Support\Facades\DB::table('sh_zones')->exists()) {
        \Illuminate\Support\Facades\DB::table('sh_zones')->insert([
            'id' => 1,
            'name' => 'Default Zone',
            'code' => 'DEFAULT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Sanity: no orders yet
    expect(Order::count())->toBe(0);

    // Run the seeder
    $this->seed(OrderSeeder::class);

    // Assertions
    $order = Order::query()->with('items', 'shipping')->latest()->first();
    expect($order)->not->toBeNull();
    expect($order->items)->not->toBeEmpty();
    expect($order->total)->toBeGreaterThan(0.0);
});

