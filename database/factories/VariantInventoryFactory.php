<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\VariantInventory>
 */
class VariantInventoryFactory extends Factory
{
    protected $model = VariantInventory::class;

    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'stock' => $this->faker->numberBetween(0, 50),
            'reserved' => 0,
            'incoming' => $this->faker->numberBetween(0, 10),
            'threshold' => 3,
            'is_tracked' => true,
        ];
    }
}


