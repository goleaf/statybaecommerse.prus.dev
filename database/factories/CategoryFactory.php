<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->boolean(60) ? '<p>' . $this->faker->paragraphs(2, true) . '</p>' : null,
            'parent_id' => null,
            'position' => $this->faker->numberBetween(0, 100),
            'is_enabled' => true,
            'seo_title' => $this->faker->boolean(40) ? $this->faker->sentence(6) : null,
            'seo_description' => $this->faker->boolean(40) ? $this->faker->sentence(12) : null,
            'metadata' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            $paths = ['demo/category.jpg', 'demo/category.png', 'demo/tshirt.jpg'];
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $category
                        ->addMedia(Storage::disk('public')->path($path))
                        ->toMediaCollection('categories');
                    break;
                }
            }
        });
    }
}
