<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $locationIds = Location::query()->pluck('id');
        if ($locationIds->isEmpty()) {
            $this->command?->warn('InventorySeeder: no locations found, skipping.');

            return;
        }

        Product::query()->select(['id'])->orderBy('id')->chunkById(200, function ($products) use ($locationIds) {
            foreach ($products as $product) {
                foreach ($locationIds as $locId) {
                    Inventory::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'location_id' => $locId,
                        ],
                        [
                            'quantity' => random_int(5, 50),
                            'reserved' => 0,
                            'incoming' => random_int(0, 10),
                            'threshold' => 5,
                            'is_tracked' => true,
                        ]
                    );
                }
            }
        });

        $this->command?->info('InventorySeeder: seeded product stock for all locations.');
    }
}
