<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

final class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $locations = Location::query()->get();
        if ($locations->isEmpty()) {
            $this->command?->warn('InventorySeeder: no locations found, skipping.');

            return;
        }

        $locationsById = $locations->keyBy('id');
        $trackedLocationIds = $locations->pluck('id');

        Product::query()
            ->with('inventories')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($trackedLocationIds, $locationsById): void {
                $products->each(function (Product $product) use ($trackedLocationIds, $locationsById): void {
                    $missingLocationIds = $trackedLocationIds
                        ->diff($product->inventories->pluck('location_id'));

                    if ($missingLocationIds->isEmpty()) {
                        return;
                    }

                    $inventories = $missingLocationIds->map(
                        function (int $locationId) use ($product, $locationsById): Inventory {
                            return Inventory::factory()
                                ->for($product)
                                ->for($locationsById->get($locationId))
                                ->state([
                                    'is_tracked' => true,
                                    'incoming'   => fake()->numberBetween(0, 20),
                                    'threshold'  => fake()->numberBetween(5, 15),
                                ])
                                ->make();
                        }
                    );

                    $product->inventories()->saveMany($inventories->all());
                });
            });

        $this->command?->info('InventorySeeder: ensured product inventories via factories.');

        if (! Schema::hasTable('variant_inventories')) {
            return;
        }

        ProductVariant::query()
            ->with('inventories')
            ->orderBy('id')
            ->chunkById(100, function ($variants) use ($locationsById, $trackedLocationIds): void {
                $variants->each(function (ProductVariant $variant) use ($locationsById, $trackedLocationIds): void {
                    $missingVariantLocationIds = $trackedLocationIds
                        ->diff($variant->inventories->pluck('location_id'));

                    if ($missingVariantLocationIds->isEmpty()) {
                        return;
                    }

                    $variantInventories = $missingVariantLocationIds
                        ->map(function (int $locationId) use ($variant, $locationsById) {
                            $location = $locationsById->get($locationId);

                            if ($location === null) {
                                return null;
                            }

                            $stock = fake()->numberBetween(15, 150);
                            $reserved = fake()->numberBetween(0, (int) floor($stock / 3));

                            $inventory = VariantInventory::factory()
                                ->for($variant, 'variant')
                                ->for($location, 'location')
                                ->state([
                                    // Ensure inventory-style payloads stay realistic for analytics and storefronts.
                                    'stock'     => $stock,
                                    'reserved'  => $reserved,
                                    'available' => max(0, $stock - $reserved),
                                    'incoming'  => fake()->numberBetween(0, 20),
                                    'threshold' => fake()->numberBetween(5, 15),
                                    'status'    => 'active',
                                    'is_tracked' => true,
                                ])
                                ->make();

                            return $inventory;
                        })
                        ->filter();

                    if ($variantInventories->isEmpty()) {
                        return;
                    }

                    // Persist the composed variant inventories so every location exposes variant stock records.
                    $variant->inventories()->saveMany($variantInventories->all());
                });
            });

        $this->command?->info('InventorySeeder: ensured variant inventories via factories.');
    }
}
