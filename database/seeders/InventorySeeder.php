<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Seeder;

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

        Product::query()
            ->with('inventories')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($locations, $locationsById): void {
                $trackedLocationIds = $locations->pluck('id');

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
                                    'incoming' => fake()->numberBetween(0, 20),
                                    'threshold' => fake()->numberBetween(5, 15),
                                ])
                                ->make();
                        }
                    );

                    $product->inventories()->saveMany($inventories->all());
                });
            });

        $this->command?->info('InventorySeeder: ensured product inventories via factories.');
    }
}
