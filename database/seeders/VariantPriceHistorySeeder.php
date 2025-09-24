<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantPriceHistory;
use Illuminate\Database\Seeder;

final class VariantPriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Get some existing variants and users
        $variants = ProductVariant::take(10)->get();
        $users = User::take(5)->get();

        if ($variants->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No variants or users found. Please seed them first.');

            return;
        }

        // Create price history records for each variant
        foreach ($variants as $variant) {
            // Create 3-5 price history records per variant
            $historyCount = fake()->numberBetween(3, 5);
            $currentPrice = $variant->price;

            for ($i = 0; $i < $historyCount; $i++) {
                $oldPrice = $currentPrice;
                $changePercentage = fake()->randomFloat(2, -30, 30); // -30% to +30%
                $newPrice = $oldPrice * (1 + $changePercentage / 100);
                $newPrice = max(0.01, $newPrice); // Ensure positive price

                VariantPriceHistory::factory()->create([
                    'variant_id' => $variant->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'price_type' => fake()->randomElement(['regular', 'sale', 'wholesale', 'bulk']),
                    'change_reason' => fake()->randomElement([
                        'manual',
                        'automatic',
                        'promotion',
                        'cost_change',
                        'market_adjustment',
                        'seasonal',
                    ]),
                    'changed_by' => $users->random()->id,
                    'effective_from' => fake()->dateTimeBetween('-6 months', 'now'),
                    'effective_until' => fake()->optional(0.3)->dateTimeBetween('now', '+3 months'),
                ]);

                $currentPrice = $newPrice;
            }
        }

        $this->command->info('Created variant price history records.');
    }
}
