<?php declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\Translations\CategoryTranslation;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryTranslation>
 */
final class CategoryTranslationFactory extends Factory
{
    protected $model = CategoryTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => $name,
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'seo_title' => $name . ' | ' . $this->faker->company(),
            'seo_description' => $this->faker->sentence(12),
            'meta_keywords' => $this->faker->words(3),
        ];
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
