<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

final class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        // Basic percentage discount
        Discount::query()->firstOrCreate(
            ['slug' => 'summer-sale-15'],
            [
                'name' => 'Summer Sale 15%',
                'description' => 'Seasonal sale on selected items',
                'type' => 'percentage',
                'value' => 15.0,
                'is_active' => true,
                'is_enabled' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(30),
                'usage_limit' => 1000,
                'usage_count' => 0,
                'minimum_amount' => 0,
            ]
        );

        // Fixed amount discount
        Discount::query()->firstOrCreate(
            ['slug' => 'welcome-10'],
            [
                'name' => 'Welcome €10',
                'description' => 'Flat €10 off for new users',
                'type' => 'fixed',
                'value' => 10.0,
                'is_active' => true,
                'is_enabled' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(90),
                'usage_limit' => null,
                'usage_count' => 0,
                'minimum_amount' => 25.0,
            ]
        );

        // Free shipping example (represented with type free_shipping and flag)
        Discount::query()->firstOrCreate(
            ['slug' => 'free-shipping-99'],
            [
                'name' => 'Free Shipping Over €99',
                'description' => 'Free shipping when cart total exceeds €99',
                'type' => 'free_shipping',
                'value' => 0.0,
                'is_active' => true,
                'is_enabled' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(60),
                'usage_limit' => null,
                'usage_count' => 0,
                'minimum_amount' => 99.0,
                'free_shipping' => true,
            ]
        );
    }
}
