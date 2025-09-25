<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

final class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPercentageDiscount();
        $this->seedFixedDiscount();
        $this->seedFreeShippingDiscount();
    }

    private function seedPercentageDiscount(): void
    {
        Discount::factory()
            ->percentage()
            ->state([
                'slug' => 'summer-sale-15',
                'name' => 'Summer Sale 15%',
                'description' => 'Seasonal sale on selected items',
                'value' => 15.0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(30),
                'usage_limit' => 1000,
                'minimum_amount' => 0,
                'is_enabled' => true,
                'is_active' => true,
            ])
            ->firstOrCreate();
    }

    private function seedFixedDiscount(): void
    {
        Discount::factory()
            ->fixed()
            ->state([
                'slug' => 'welcome-10',
                'name' => 'Welcome €10',
                'description' => 'Flat €10 off for new users',
                'value' => 10.0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(90),
                'minimum_amount' => 25.0,
                'usage_limit' => null,
                'is_enabled' => true,
                'is_active' => true,
            ])
            ->firstOrCreate();
    }

    private function seedFreeShippingDiscount(): void
    {
        Discount::factory()
            ->state([
                'slug' => 'free-shipping-99',
                'name' => 'Free Shipping Over €99',
                'description' => 'Free shipping when cart total exceeds €99',
                'type' => 'free_shipping',
                'value' => 0.0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(60),
                'minimum_amount' => 99.0,
                'free_shipping' => true,
                'usage_limit' => null,
                'is_enabled' => true,
                'is_active' => true,
            ])
            ->firstOrCreate();
    }
}
