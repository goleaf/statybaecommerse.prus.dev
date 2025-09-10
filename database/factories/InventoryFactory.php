<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'location_id' => Location::factory(),
            'quantity' => $this->faker->numberBetween(0, 50),
            'reserved' => 0,
            'incoming' => $this->faker->numberBetween(0, 10),
            'threshold' => 5,
            'is_tracked' => true,
        ];
    }
}
