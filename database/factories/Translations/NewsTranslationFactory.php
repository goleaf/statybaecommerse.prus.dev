<?php

declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\Translations\NewsTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Translations\NewsTranslation>
 */
final class NewsTranslationFactory extends Factory
{
    protected $model = NewsTranslation::class;

    public function definition(): array
    {
        $locale = fake()->randomElement(['lt', 'en']);
        $title = $locale === 'lt'
            ? fake('lt_LT')->sentence(3)
            : fake('en_US')->sentence(3);

        return [
            'locale' => $locale,
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
            'seo_title' => $title,
            'seo_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ];
    }
}
