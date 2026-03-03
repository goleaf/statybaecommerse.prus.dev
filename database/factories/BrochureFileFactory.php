<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brochure;
use App\Models\BrochureFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrochureFile>
 */
class BrochureFileFactory extends Factory
{
    protected $model = BrochureFile::class;

    public function definition(): array
    {
        return [
            'brochure_id' => Brochure::factory(),
            'name'        => $this->faker->sentence(2),
            'file_path'   => 'brochures/' . $this->faker->uuid() . '.pdf',
            'is_active'   => true,
            'sort_order'  => $this->faker->numberBetween(0, 25),
        ];
    }
}
