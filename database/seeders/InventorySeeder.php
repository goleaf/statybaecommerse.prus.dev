<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Support\Facades\Schema;

final class InventorySeeder extends BaseSeeder
{
    public function run(): void
    {
        if (! Product::query()->exists()) {
            $this->call(ProductSeeder::class);
        }

        if (! Location::query()->where('type', 'warehouse')->exists()) {
            $this->call(WarehouseSeeder::class);
        }

        $warehouses = Location::query()
            ->where('type', 'warehouse')
            ->get();

        if ($warehouses->isEmpty()) {
            $this->command?->warn('InventorySeeder: no warehouses found, skipping.');

            return;
        }

        $warehousesById = $warehouses->keyBy('id');
        $warehouseIds = $warehouses->pluck('id');

        Product::query()
            ->with('inventories')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($warehouseIds, $warehousesById): void {
                $products->each(function (Product $product) use ($warehouseIds, $warehousesById): void {
                    $missingWarehouseIds = $warehouseIds
                        ->diff($product->inventories->pluck('warehouse_id'));

                    if ($missingWarehouseIds->isEmpty()) {
                        return;
                    }

                    $inventories = $missingWarehouseIds->map(
                        function (int $warehouseId) use ($product, $warehousesById): Inventory {
                            $warehouse = $warehousesById->get($warehouseId);

                            if ($warehouse === null) {
                                return Inventory::factory()->make();
                            }

                            return Inventory::factory()
                                ->for($product)
                                ->for($warehouse, 'warehouse')
                                ->state([
                                    'sku'        => sprintf('%s-%s', (string) ($product->sku ?? 'PRD'), (string) ($warehouse->code ?? 'WH')),
                                    'is_tracked' => true,
                                    'incoming'   => fake()->numberBetween(0, 20),
                                    'threshold'  => fake()->numberBetween(5, 15),
                                ])
                                ->make();
                        }
                    )->filter(static fn (Inventory $inventory): bool => $inventory->warehouse_id !== null);

                    $product->inventories()->saveMany($inventories->all());
                });
            });

        $this->command?->info('InventorySeeder: ensured product inventories for every product and warehouse.');

        if (! Schema::hasTable('variant_inventories')) {
            return;
        }

        ProductVariant::query()
            ->with('inventories')
            ->orderBy('id')
            ->chunkById(100, function ($variants) use ($warehousesById, $warehouseIds): void {
                $variants->each(function (ProductVariant $variant) use ($warehousesById, $warehouseIds): void {
                    $missingVariantLocationIds = $warehouseIds
                        ->diff($variant->inventories->pluck('location_id'));

                    if ($missingVariantLocationIds->isEmpty()) {
                        return;
                    }

                    $variantInventories = $missingVariantLocationIds
                        ->map(function (int $locationId) use ($variant, $warehousesById) {
                            $location = $warehousesById->get($locationId);

                            if ($location === null) {
                                return null;
                            }

                            $stock = fake()->numberBetween(15, 150);
                            $reserved = fake()->numberBetween(0, (int) floor($stock / 3));

                            $inventory = VariantInventory::factory()
                                ->for($variant, 'variant')
                                ->for($location, 'location')
                                ->state([
                                    // Ensure inventory-style payloads stay realistic for reporting and storefronts.
                                    'stock'      => $stock,
                                    'reserved'   => $reserved,
                                    'available'  => max(0, $stock - $reserved),
                                    'incoming'   => fake()->numberBetween(0, 20),
                                    'threshold'  => fake()->numberBetween(5, 15),
                                    'status'     => 'active',
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
