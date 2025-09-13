<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Translations\CollectionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Translations\CollectionTranslation>
 */
class CollectionTranslationFactory extends Factory
{
    protected $model = CollectionTranslation::class;

    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'seo_title' => $this->faker->sentence(6),
            'seo_description' => $this->faker->sentence(12),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->sentence(12),
            'meta_keywords' => $this->faker->words(3),
        ];
    }
}
