<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VariantInventorySeeder
 *
 * Comprehensive seeder for VariantInventory with realistic data
 * including various stock levels, locations, and statuses.
 */
final class VariantInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = ProductVariant::all();
        $locations = Location::all();

        if ($variants->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('No variants or locations found. Please run ProductVariantSeeder and LocationSeeder first.');

            return;
        }

        $partnerIds = DB::table('partners')->pluck('id')->all();

        $inventoryData = [];
        $usedCodesByVariant = [];

        foreach ($variants as $variant) {
            foreach ($locations as $location) {
                // Skip some combinations to make it more realistic
                if (fake()->boolean(70)) {
                    $stockLevel = fake()->numberBetween(0, 500);
                    $reserved = fake()->numberBetween(0, min(50, $stockLevel));
                    $available = max(0, $stockLevel - $reserved);

                    $supplierId = null;
                    if (!empty($partnerIds)) {
                        $supplierId = $partnerIds[array_rand($partnerIds)];
                    }

                    $inventoryData[] = [
                        'variant_id' => $variant->id,
                        'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id, $usedCodesByVariant),
                        'stock' => $stockLevel,
                        'reserved' => $reserved,
                        'available' => $available,
                        'reorder_point' => fake()->numberBetween(5, 25),
                        'reorder_quantity' => fake()->numberBetween(50, 200),
                        'max_stock_level' => fake()->numberBetween(300, 1000),
                        'cost_per_unit' => fake()->randomFloat(2, 5, 100),
                        'supplier_id' => $supplierId,
                        'batch_number' => fake()->optional(0.8)->numerify('BATCH-####'),
                        'expiry_date' => fake()->optional(0.6)->dateTimeBetween('+1 month', '+2 years'),
                        'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']),  // 75% active
                        'notes' => fake()->optional(0.3)->sentence(),
                        'last_restocked_at' => fake()->optional(0.7)->dateTimeBetween('-6 months', 'now'),
                        'last_sold_at' => fake()->optional(0.8)->dateTimeBetween('-3 months', 'now'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($inventoryData, 100);
        foreach ($chunks as $chunk) {
            VariantInventory::insert($chunk);
        }

        // Create some specific scenarios for testing (after base insert to avoid collisions)
        $this->createSpecificScenarios($variants, $locations);

        $this->command->info('VariantInventory seeded successfully with ' . count($inventoryData) . ' records.');
    }

    /**
     * Create specific scenarios for testing and demonstration
     */
    private function createSpecificScenarios($variants, $locations): void
    {
        // Low stock scenarios
        $lowStockVariants = $variants->random(5);
        foreach ($lowStockVariants as $variant) {
            $location = $locations->random();
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => fake()->numberBetween(1, 10),
                'available' => fake()->numberBetween(1, 10),
                'reorder_point' => fake()->numberBetween(10, 20),
                'status' => 'active',
                'notes' => 'Low stock - needs reorder',
            ]);
        }

        // Out of stock scenarios
        $outOfStockVariants = $variants->random(3);
        foreach ($outOfStockVariants as $variant) {
            $location = $locations->random();
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => 0,
                'available' => 0,
                'reserved' => 0,
                'reorder_point' => fake()->numberBetween(5, 15),
                'status' => 'active',
                'notes' => 'Out of stock - urgent reorder needed',
            ]);
        }

        // High utilization scenarios
        $highUtilizationVariants = $variants->random(4);
        foreach ($highUtilizationVariants as $variant) {
            $location = $locations->random();
            $stock = fake()->numberBetween(50, 100);
            $reserved = fake()->numberBetween(40, $stock - 5);
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => $stock,
                'reserved' => $reserved,
                'available' => $stock - $reserved,
                'reorder_point' => fake()->numberBetween(5, 15),
                'status' => 'active',
                'notes' => 'High utilization - monitor closely',
            ]);
        }

        // Expiring soon scenarios
        $expiringVariants = $variants->random(6);
        foreach ($expiringVariants as $variant) {
            $location = $locations->random();
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => fake()->numberBetween(20, 80),
                'available' => fake()->numberBetween(15, 75),
                'expiry_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
                'status' => 'active',
                'notes' => 'Expires soon - consider promotion',
            ]);
        }

        // Discontinued items
        $discontinuedVariants = $variants->random(2);
        foreach ($discontinuedVariants as $variant) {
            $location = $locations->random();
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => fake()->numberBetween(5, 30),
                'available' => fake()->numberBetween(5, 30),
                'status' => 'discontinued',
                'notes' => 'Discontinued - clear remaining stock',
            ]);
        }

        // Untracked items
        $untrackedVariants = $variants->random(3);
        foreach ($untrackedVariants as $variant) {
            $location = $locations->random();
            VariantInventory::factory()->create([
                'variant_id' => $variant->id,
                'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                'stock' => fake()->numberBetween(10, 50),
                'available' => fake()->numberBetween(10, 50),
                'status' => 'active',
                'notes' => 'Not tracked - manual management',
            ]);
        }
    }

    /**
     * Generate warehouse code based on location
     */
    private function generateWarehouseCodeUnique(Location $location, int $variantId, ?array &$usedCodesByVariant = null): string
    {
        $prefix = strtoupper(substr($location->code ?? $location->name, 0, 3));
        do {
            $code = $prefix . '-' . fake()->numerify('###');
            $existsInDb = VariantInventory::query()
                ->where('variant_id', $variantId)
                ->where('warehouse_code', $code)
                ->exists();
            $existsInMemory = is_array($usedCodesByVariant) &&
                in_array($code, $usedCodesByVariant[$variantId] ?? [], true);
        } while ($existsInDb || $existsInMemory);

        if (is_array($usedCodesByVariant)) {
            $usedCodesByVariant[$variantId] ??= [];
            $usedCodesByVariant[$variantId][] = $code;
        }

        return $code;
    }
}
