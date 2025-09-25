<?php

declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\NewsTag;
use App\Models\Translations\NewsTagTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsTagTranslation>
 */
final class NewsTagTranslationFactory extends Factory
{
    protected $model = NewsTagTranslation::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'news_tag_id' => NewsTag::factory(),
            'locale' => fake()->randomElement(['lt', 'en']),
            'name' => $name,
            'description' => fake()->sentence(),
        ];
    }

    public function forNewsTag(NewsTag $newsTag): static
    {
        return $this->state(fn (array $attributes) => [
            'news_tag_id' => $newsTag->id,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
