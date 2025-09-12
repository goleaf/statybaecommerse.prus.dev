<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Translations\BrandTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\BrandTranslation>
 */
final class BrandTranslationFactory extends Factory
{
    protected $model = BrandTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'brand_id' => Brand::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'ru', 'de']),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
        ];
    }

    /**
     * Create a Lithuanian translation
     */
    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->company() . ' (LT)',
            'slug' => Str::slug($attributes['name'] ?? $this->faker->company()) . '-lt',
            'description' => 'Lietuviškas aprašymas: ' . $this->faker->paragraph(),
        ]);
    }

    /**
     * Create an English translation
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $attributes['name'] ?? $this->faker->company(),
            'slug' => Str::slug($attributes['name'] ?? $this->faker->company()),
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a Russian translation
     */
    public function russian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'ru',
            'name' => $this->faker->company() . ' (RU)',
            'slug' => Str::slug($attributes['name'] ?? $this->faker->company()) . '-ru',
            'description' => 'Русское описание: ' . $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a German translation
     */
    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
            'name' => $this->faker->company() . ' (DE)',
            'slug' => Str::slug($attributes['name'] ?? $this->faker->company()) . '-de',
            'description' => 'Deutsche Beschreibung: ' . $this->faker->paragraph(),
        ]);
    }
}