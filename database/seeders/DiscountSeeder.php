<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

final class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $this->upsertDiscount([
            'slug' => 'summer-sale-15',
            'name' => 'Summer Sale 15%',
            'description' => 'Seasonal sale on selected items',
            'type' => 'percentage',
            'value' => 15.0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'usage_limit' => 1000,
            'usage_count' => 0,
            'minimum_amount' => 0,
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $this->upsertDiscount([
            'slug' => 'welcome-10',
            'name' => 'Welcome €10',
            'description' => 'Flat €10 off for new users',
            'type' => 'fixed',
            'value' => 10.0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(90),
            'usage_limit' => null,
            'usage_count' => 0,
            'minimum_amount' => 25.0,
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $this->upsertDiscount([
            'slug' => 'free-shipping-99',
            'name' => 'Free Shipping Over €99',
            'description' => 'Free shipping when cart total exceeds €99',
            'type' => 'free_shipping',
            'value' => 0.0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(60),
            'usage_limit' => null,
            'usage_count' => 0,
            'minimum_amount' => 99.0,
            'free_shipping' => true,
            'is_enabled' => true,
            'is_active' => true,
        ]);
    }

    private function upsertDiscount(array $state): void
    {
        // Check if discount already exists to maintain idempotency
        $existingDiscount = Discount::withoutGlobalScopes()->where('slug', $state['slug'])->first();

        if (! $existingDiscount) {
            Discount::factory()
                ->state($state)
                ->create();
        }
    }
}
