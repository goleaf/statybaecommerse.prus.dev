<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
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
            'slug' => Str::slug($name . '-' . $this->faker->unique()->randomNumber()),
            'description' => $this->faker->boolean(60) ? '<p>' . $this->faker->paragraphs(2, true) . '</p>' : null,
            'is_visible' => true,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'seo_title' => $this->faker->boolean(40) ? $this->faker->sentence(6) : null,
            'seo_description' => $this->faker->boolean(40) ? $this->faker->sentence(12) : null,
            'is_automatic' => null,
        ];
    }

    public function configure(): static
    {
        // Skip media creation to avoid GD errors in testing
        return $this;
    }
}
