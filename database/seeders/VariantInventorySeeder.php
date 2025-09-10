<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;

final class VariantInventorySeeder extends Seeder
{
    public function run(): void
    {
        $locations = Location::query()->pluck('id');
        if ($locations->isEmpty()) {
            $this->command?->warn('VariantInventorySeeder: no locations found, skipping.');
            return;
        }

        ProductVariant::query()->select('id')->orderBy('id')->chunkById(200, function ($variants) use ($locations) {
            foreach ($variants as $variant) {
                foreach ($locations as $locationId) {
                    VariantInventory::updateOrCreate(
                        [
                            'variant_id' => $variant->id,
                            'location_id' => $locationId,
                        ],
                        [
                            'stock' => random_int(0, 30),
                            'reserved' => 0,
                            'incoming' => random_int(0, 5),
                            'threshold' => 3,
                            'is_tracked' => true,
                        ]
                    );
                }
            }
        });

        $this->command?->info('VariantInventorySeeder: seeded variant stock for all locations.');
    }
}


