<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name.'-'.$this->faker->unique()->randomNumber()),
            'description' => $this->faker->boolean(60) ? '<p>'.$this->faker->paragraphs(2, true).'</p>' : null,
            'is_visible' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'seo_title' => $this->faker->boolean(40) ? $this->faker->sentence(6) : null,
            'seo_description' => $this->faker->boolean(40) ? $this->faker->sentence(12) : null,
            'is_automatic' => $this->faker->boolean(30),
            'rules' => null,
            'max_products' => $this->faker->boolean(30) ? $this->faker->numberBetween(5, 50) : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Collection $collection): void {
            if (app()->environment('testing')) {
                return;  // skip media in tests to avoid GD errors
            }
            $paths = ['demo/collection.jpg', 'demo/collection.png', 'demo/tshirt.jpg'];
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $collection
                        ->addMedia(Storage::disk('public')->path($path))
                        ->toMediaCollection('images');
                    break;
                }
            }
        });
    }
}
