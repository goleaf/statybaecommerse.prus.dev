<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => $this->faker->boolean(70) ? $this->faker->url() : null,
            'description' => $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'position' => $this->faker->numberBetween(0, 100),
            'is_enabled' => true,
            'seo_title' => $this->faker->boolean(40) ? $this->faker->sentence(6) : null,
            'seo_description' => $this->faker->boolean(40) ? $this->faker->sentence(12) : null,
            'metadata' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Brand $brand): void {
            $paths = ['demo/brand.png', 'demo/brand.jpg', 'demo/tshirt.jpg'];
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $brand
                        ->addMedia(Storage::disk('public')->path($path))
                        ->toMediaCollection('brands');
                    break;
                }
            }
        });
    }
}
