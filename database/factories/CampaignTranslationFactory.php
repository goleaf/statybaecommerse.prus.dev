<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Translations\CampaignTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\CampaignTranslation>
 */
final class CampaignTranslationFactory extends Factory
{
    protected $model = CampaignTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        return [
            'campaign_id' => Campaign::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'de']),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->paragraph(3),
            'subject' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(5, true),
            'cta_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started', 'Sign Up', 'Buy Now']),
            'banner_alt_text' => $this->faker->sentence(6),
            'meta_title' => $this->faker->sentence(8),
            'meta_description' => $this->faker->paragraph(1),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'subject' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(5, true),
            'cta_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started']),
            'meta_title' => $this->faker->sentence(8),
            'meta_description' => $this->faker->paragraph(1),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => 'Lietuviškas kampanijos pavadinimas',
            'description' => 'Lietuviškas kampanijos aprašymas su daugiau informacijos.',
            'subject' => 'Lietuviška kampanijos tema',
            'content' => 'Lietuviškas kampanijos turinys su išsamia informacija.',
            'cta_text' => $this->faker->randomElement(['Pirkti dabar', 'Sužinoti daugiau', 'Pradėti']),
            'meta_title' => 'Lietuviškas meta pavadinimas',
            'meta_description' => 'Lietuviškas meta aprašymas kampanijai.',
        ]);
    }

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
            'name' => 'Deutscher Kampagnenname',
            'description' => 'Deutsche Kampagnenbeschreibung mit mehr Informationen.',
            'subject' => 'Deutsches Kampagnenthema',
            'content' => 'Deutscher Kampagneninhalt mit detaillierten Informationen.',
            'cta_text' => $this->faker->randomElement(['Jetzt kaufen', 'Mehr erfahren', 'Loslegen']),
            'meta_title' => 'Deutscher Meta-Titel',
            'meta_description' => 'Deutsche Meta-Beschreibung für die Kampagne.',
        ]);
    }
}
