<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brochure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brochure>
 */
class BrochureFactory extends Factory
{
    protected $model = Brochure::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->unique()->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'is_active'   => true,
            'sort_order'  => $this->faker->numberBetween(0, 50),
        ];
    }
}
