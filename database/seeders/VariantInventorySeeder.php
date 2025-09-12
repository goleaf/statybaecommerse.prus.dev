<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Partner;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;

class VariantInventorySeeder extends Seeder
{
    public function run(): void
    {
        // Get existing variants and locations
        $variants = ProductVariant::with('product')->get();
        $locations = Location::enabled()->get();
        $suppliers = Partner::where('type', 'supplier')->get();

        if ($variants->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('No variants or locations found. Skipping VariantInventory seeding.');

            return;
        }

        $this->command->info('Creating variant inventory records...');

        $inventoryCount = 0;

        foreach ($variants as $variant) {
            foreach ($locations as $location) {
                // Create inventory for each variant-location combination
                $inventory = VariantInventory::factory()->create([
                    'variant_id' => $variant->id,
                    'location_id' => $location->id,
                    'stock' => fake()->numberBetween(0, 500),
                    'reserved' => fake()->numberBetween(0, 50),
                    'incoming' => fake()->numberBetween(0, 100),
                    'threshold' => fake()->numberBetween(10, 50),
                    'reorder_point' => fake()->numberBetween(5, 25),
                    'max_stock_level' => fake()->numberBetween(200, 1000),
                    'cost_per_unit' => fake()->randomFloat(2, 5, 100),
                    'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->random()->id : null,
                    'batch_number' => fake()->optional(0.7)->regexify('[A-Z0-9]{8}'),
                    'expiry_date' => fake()->optional(0.3)->dateTimeBetween('now', '+2 years'),
                    'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
                    'is_tracked' => fake()->boolean(85), // 85% tracked
                    'notes' => fake()->optional(0.4)->sentence(),
                    'last_restocked_at' => fake()->optional(0.6)->dateTimeBetween('-6 months', 'now'),
                    'last_sold_at' => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
                ]);

                $inventoryCount++;

                // Create some stock movements for this inventory
                if (fake()->boolean(70)) {
                    $movementCount = fake()->numberBetween(1, 5);

                    for ($i = 0; $i < $movementCount; $i++) {
                        $inventory->stockMovements()->create([
                            'quantity' => fake()->numberBetween(1, 50),
                            'type' => fake()->randomElement(['in', 'out']),
                            'reason' => fake()->randomElement([
                                'sale',
                                'return',
                                'adjustment',
                                'manual_adjustment',
                                'restock',
                                'damage',
                                'transfer',
                            ]),
                            'reference' => fake()->optional(0.6)->regexify('[A-Z0-9]{6}'),
                            'notes' => fake()->optional(0.3)->sentence(),
                            'user_id' => null, // System generated
                            'moved_at' => fake()->dateTimeBetween('-3 months', 'now'),
                        ]);
                    }
                }
            }
        }

        $this->command->info("Created {$inventoryCount} variant inventory records.");

        // Create some specific scenarios for testing
        $this->createTestScenarios($variants, $locations, $suppliers);
    }

    private function createTestScenarios($variants, $locations, $suppliers): void
    {
        $this->command->info('Creating test scenarios...');

        // Low stock scenario
        if ($variants->isNotEmpty() && $locations->isNotEmpty()) {
            VariantInventory::factory()->lowStock()->create([
                'variant_id' => $variants->first()->id,
                'location_id' => $locations->first()->id,
                'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->first()->id : null,
            ]);
        }

        // Out of stock scenario
        if ($variants->count() > 1 && $locations->isNotEmpty()) {
            VariantInventory::factory()->outOfStock()->create([
                'variant_id' => $variants->skip(1)->first()->id,
                'location_id' => $locations->first()->id,
                'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->first()->id : null,
            ]);
        }

        // Expiring soon scenario
        if ($variants->count() > 2 && $locations->isNotEmpty()) {
            VariantInventory::factory()->expiringSoon()->create([
                'variant_id' => $variants->skip(2)->first()->id,
                'location_id' => $locations->first()->id,
                'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->first()->id : null,
            ]);
        }

        // Needs reorder scenario
        if ($variants->count() > 3 && $locations->isNotEmpty()) {
            VariantInventory::factory()->needsReorder()->create([
                'variant_id' => $variants->skip(3)->first()->id,
                'location_id' => $locations->first()->id,
                'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->first()->id : null,
            ]);
        }

        $this->command->info('Test scenarios created successfully.');
    }
}
