<?php

declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\Translations\NewsCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\NewsCategoryTranslation>
 */
final class NewsCategoryTranslationFactory extends Factory
{
    protected $model = NewsCategoryTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'news_category_id' => null,
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => ucwords($name),
            'slug' => str($name)->slug()->toString(),
            'description' => $this->faker->paragraph(2),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->randomElement([
                'Naujienos',
                'Projektai',
                'Patarimai',
                'Technologijos',
                'Statyba',
                'Dizainas',
                'Aktualijos',
                'Prekyba',
                'Paslaugos',
                'Partneriai',
            ]),
            'slug' => str($this->faker->words(1, true))->slug()->toString(),
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->randomElement([
                'News',
                'Projects',
                'Tips',
                'Technology',
                'Construction',
                'Design',
                'Current Affairs',
                'Commerce',
                'Services',
                'Partners',
            ]),
            'slug' => str($this->faker->words(1, true))->slug()->toString(),
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ]);
    }
}
