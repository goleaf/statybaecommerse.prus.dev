<?php declare(strict_types=1);

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
        $title = fake()->sentence(3);
        return [
            'locale' => fake()->randomElement(['lt', 'en']),
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
            'seo_title' => $title,
            'seo_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ];
    }
}
