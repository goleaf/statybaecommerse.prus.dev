<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Translations\LegalTranslation>
 */
class LegalTranslationFactory extends Factory
{
    protected $model = LegalTranslation::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        $slug = \Illuminate\Support\Str::slug($title).'-'.fake()->randomElement(['lt', 'en']);

        return [
            'legal_id' => Legal::factory(),
            'locale' => fake()->randomElement(['lt', 'en']),
            'title' => $title,
            'slug' => $slug,
            'content' => '<p>'.fake()->paragraphs(5, true).'</p>',
            'seo_title' => fake()->sentence(2),
            'seo_description' => fake()->sentence(10),
            'meta_data' => [
                'word_count' => fake()->numberBetween(100, 2000),
                'reading_time' => fake()->numberBetween(1, 10),
                'last_reviewed' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            ],
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'title' => fake('lt_LT')->sentence(3),
            'slug' => \Illuminate\Support\Str::slug(fake('lt_LT')->sentence(3)).'-lt',
            'content' => '<p>'.fake('lt_LT')->paragraphs(5, true).'</p>',
            'seo_title' => fake('lt_LT')->sentence(2),
            'seo_description' => fake('lt_LT')->sentence(10),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'title' => fake()->sentence(3),
            'slug' => \Illuminate\Support\Str::slug(fake()->sentence(3)).'-en',
            'content' => '<p>'.fake()->paragraphs(5, true).'</p>',
            'seo_title' => fake()->sentence(2),
            'seo_description' => fake()->sentence(10),
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement([
                'Privatumo politika',
                'Privacy Policy',
            ]),
            'content' => '<h2>Privatumo politika</h2><p>'.fake()->paragraphs(10, true).'</p>',
        ]);
    }

    public function termsOfUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement([
                'Naudojimosi sąlygos',
                'Terms of Use',
            ]),
            'content' => '<h2>Naudojimosi sąlygos</h2><p>'.fake()->paragraphs(8, true).'</p>',
        ]);
    }

    public function refundPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement([
                'Grąžinimo politika',
                'Refund Policy',
            ]),
            'content' => '<h2>Grąžinimo politika</h2><p>'.fake()->paragraphs(6, true).'</p>',
        ]);
    }

    public function shippingPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement([
                'Pristatymo politika',
                'Shipping Policy',
            ]),
            'content' => '<h2>Pristatymo politika</h2><p>'.fake()->paragraphs(7, true).'</p>',
        ]);
    }
}
