<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;

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
        $variants = ProductVariant::query()->with(['inventories' => fn($query) => $query->select('id', 'variant_id', 'location_id')])->get();
        $locations = Location::query()->get();

        if ($variants->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('No variants or locations found. Please run ProductVariantSeeder and LocationSeeder first.');

            return;
        }

        foreach ($variants as $variant) {
            foreach ($locations as $location) {
                if ($variant->inventories->contains(fn(VariantInventory $inventory): bool => $inventory->location_id === $location->id)) {
                    continue;
                }

                $stock = fake()->numberBetween(15, 120);
                $reserved = fake()->numberBetween(0, (int) floor($stock / 3));

                $variant
                    ->inventories()
                    ->create([
                        'location_id' => $location->id,
                        'warehouse_code' => $this->generateWarehouseCodeUnique($location, $variant->id),
                        'stock' => $stock,
                        'reserved' => $reserved,
                        'available' => max(0, $stock - $reserved),
                        'reorder_point' => fake()->numberBetween(5, 25),
                        'reorder_quantity' => fake()->numberBetween(25, 75),
                        'max_stock_level' => fake()->numberBetween(200, 400),
                        'cost_per_unit' => fake()->randomFloat(2, 5, 150),
                        'batch_number' => fake()->optional(0.5)->regexify('[A-Z]{3}-[0-9]{4}'),
                        'expiry_date' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
                        'status' => fake()->randomElement(['active', 'active', 'inactive']),
                        'notes' => fake()->optional(0.3)->sentence(),
                        'last_restocked_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
                        'last_sold_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
                    ]);
            }
        }

        $this->command?->info('VariantInventorySeeder: ensured variant inventories via factories and relationships.');
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
