<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * VariantInventorySeeder
 *
 * Comprehensive seeder for VariantInventory with realistic data
 * including various stock levels, locations, and statuses.
 */
final class VariantInventorySeeder extends \Database\Seeders\BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('🏭 Starting Variant Inventory seeding...');

        // Get or create locations
        $locations = $this->getOrCreateLocations();

        if ($locations->isEmpty()) {
            $this->command?->error('❌ No locations available. Please run LocationSeeder first.');

            return;
        }

        // Get or create product variants
        $variants = $this->getOrCreateVariants();

        if ($variants->isEmpty()) {
            $this->command?->error('❌ No product variants available. Please run ProductVariantSeeder first.');

            return;
        }

        // Create inventory records for each variant-location combination
        $this->createVariantInventories($variants, $locations);

        // Create specific test scenarios
        $this->createSpecificScenarios($variants, $locations);

        $this->command?->info('✅ VariantInventorySeeder: completed successfully!');
    }

    private function getOrCreateLocations(): Collection
    {
        $existingCount = Location::count();

        if ($existingCount >= 5) {
            $this->command?->info("✓ Using {$existingCount} existing locations");

            return Location::limit(5)->get();
        }

        $needed = 5 - $existingCount;
        $this->command?->info("Creating {$needed} locations...");

        Location::factory()->count($needed)->create();

        return Location::limit(5)->get();
    }

    private function getOrCreateVariants(): Collection
    {
        $existingCount = ProductVariant::count();

        if ($existingCount >= 20) {
            $this->command?->info("✓ Using {$existingCount} existing product variants");

            return ProductVariant::limit(20)->get();
        }

        $needed = 20 - $existingCount;
        $this->command?->info("Creating {$needed} product variants...");

        ProductVariant::factory()->count($needed)->create();

        return ProductVariant::limit(20)->get();
    }

    private function createVariantInventories(Collection $variants, Collection $locations): void
    {
        $this->command?->info('Creating variant inventories for each location...');

        $created = 0;
        $skipped = 0;

        $variants->each(function (ProductVariant $variant) use ($locations, &$created, &$skipped): void {
            $locations->each(function (Location $location) use ($variant, &$created, &$skipped): void {
                // Check if inventory already exists for this variant-location combination
                $exists = VariantInventory::where('variant_id', $variant->id)
                    ->where('location_id', $location->id)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    return;
                }

                VariantInventory::factory()
                    ->for($variant, 'variant')
                    ->for($location, 'location')
                    ->create();

                $created++;
            });
        });

        if ($created > 0) {
            $this->command?->info("✓ Created {$created} variant inventories");
        }

        if ($skipped > 0) {
            $this->command?->info("✓ Skipped {$skipped} existing variant inventories");
        }
    }

    /**
     * Create specific scenarios for testing and demonstration
     */
    private function createSpecificScenarios(Collection $variants, Collection $locations): void
    {
        $this->command?->info('Creating specific inventory scenarios...');

        $scenariosCreated = 0;

        // Low stock scenarios
        $scenariosCreated += $this->createLowStockScenarios($variants, $locations);

        // Out of stock scenarios
        $scenariosCreated += $this->createOutOfStockScenarios($variants, $locations);

        // High utilization scenarios
        $scenariosCreated += $this->createHighUtilizationScenarios($variants, $locations);

        // Expiring soon scenarios
        $scenariosCreated += $this->createExpiringSoonScenarios($variants, $locations);

        // Discontinued items
        $scenariosCreated += $this->createDiscontinuedScenarios($variants, $locations);

        // Untracked items
        $scenariosCreated += $this->createUntrackedScenarios($variants, $locations);

        $this->command?->info("✓ Created {$scenariosCreated} specific scenarios");
    }

    private function createLowStockScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $lowStockVariants = $variants->random(min(5, $variants->count()));

        foreach ($lowStockVariants as $variant) {
            $location = $locations->random();

            // Skip if already has inventory at this location
            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => fake()->numberBetween(1, 10),
                'available'      => fake()->numberBetween(1, 10),
                'reorder_point'  => fake()->numberBetween(10, 20),
                'status'         => 'active',
                'notes'          => 'Low stock - needs reorder',
            ]);
            $created++;
        }

        return $created;
    }

    private function createOutOfStockScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $outOfStockVariants = $variants->random(min(3, $variants->count()));

        foreach ($outOfStockVariants as $variant) {
            $location = $locations->random();

            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => 0,
                'available'      => 0,
                'reserved'       => 0,
                'reorder_point'  => fake()->numberBetween(5, 15),
                'status'         => 'active',
                'notes'          => 'Out of stock - urgent reorder needed',
            ]);
            $created++;
        }

        return $created;
    }

    private function createHighUtilizationScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $highUtilizationVariants = $variants->random(min(4, $variants->count()));

        foreach ($highUtilizationVariants as $variant) {
            $location = $locations->random();

            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            $stock = fake()->numberBetween(50, 100);
            $reserved = fake()->numberBetween(40, $stock - 5);

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => $stock,
                'reserved'       => $reserved,
                'available'      => $stock - $reserved,
                'reorder_point'  => fake()->numberBetween(5, 15),
                'status'         => 'active',
                'notes'          => 'High utilization - monitor closely',
            ]);
            $created++;
        }

        return $created;
    }

    private function createExpiringSoonScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $expiringVariants = $variants->random(min(6, $variants->count()));

        foreach ($expiringVariants as $variant) {
            $location = $locations->random();

            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => fake()->numberBetween(20, 80),
                'available'      => fake()->numberBetween(15, 75),
                'expiry_date'    => fake()->dateTimeBetween('+1 week', '+1 month'),
                'status'         => 'active',
                'notes'          => 'Expires soon - consider promotion',
            ]);
            $created++;
        }

        return $created;
    }

    private function createDiscontinuedScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $discontinuedVariants = $variants->random(min(2, $variants->count()));

        foreach ($discontinuedVariants as $variant) {
            $location = $locations->random();

            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => fake()->numberBetween(5, 30),
                'available'      => fake()->numberBetween(5, 30),
                'status'         => 'discontinued',
                'notes'          => 'Discontinued - clear remaining stock',
            ]);
            $created++;
        }

        return $created;
    }

    private function createUntrackedScenarios(Collection $variants, Collection $locations): int
    {
        $created = 0;
        $untrackedVariants = $variants->random(min(3, $variants->count()));

        foreach ($untrackedVariants as $variant) {
            $location = $locations->random();

            if ($this->hasInventory($variant->id, $location->id)) {
                continue;
            }

            VariantInventory::factory()->create([
                'variant_id'     => $variant->id,
                'location_id'    => $location->id,
                'warehouse_code' => $this->generateWarehouseCode($location, $variant->id),
                'stock'          => fake()->numberBetween(10, 50),
                'available'      => fake()->numberBetween(10, 50),
                'status'         => 'active',
                'notes'          => 'Not tracked - manual management',
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Check if inventory already exists for variant-location combination
     */
    private function hasInventory(int $variantId, int $locationId): bool
    {
        return VariantInventory::where('variant_id', $variantId)
            ->where('location_id', $locationId)
            ->exists();
    }

    /**
     * Generate unique warehouse code based on location
     */
    private function generateWarehouseCode(Location $location, int $variantId): string
    {
        $prefix = strtoupper(substr($location->code ?? $location->name, 0, 3));

        do {
            $code = $prefix . '-' . fake()->numerify('###');
            $exists = VariantInventory::where('variant_id', $variantId)
                ->where('warehouse_code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }
}
