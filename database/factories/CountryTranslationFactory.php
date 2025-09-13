<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\CountryTranslation>
 */
final class CountryTranslationFactory extends Factory
{
    protected $model = CountryTranslation::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'locale' => fake()->randomElement(['en', 'lt', 'de', 'fr', 'es']),
            'name' => fake()->country(),
            'name_official' => fake()->country() . ' Republic',
            'description' => fake()->paragraph(),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => fake()->randomElement([
                'Lietuva',
                'Latvija', 
                'Estija',
                'Lenkija',
                'Vokietija',
                'Prancūzija',
                'Italija',
                'Ispanija',
            ]),
            'name_official' => fake()->randomElement([
                'Lietuvos Respublika',
                'Latvijos Respublika',
                'Estijos Respublika',
                'Lenkijos Respublika',
                'Vokietijos Federacinė Respublika',
                'Prancūzijos Respublika',
                'Italijos Respublika',
                'Ispanijos Karalystė',
            ]),
            'description' => fake()->randomElement([
                'Šiaurės Europos šalis',
                'Rytų Europos šalis',
                'Vakarų Europos šalis',
                'Pietų Europos šalis',
                'Centrinės Europos šalis',
            ]),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => fake()->country(),
            'name_official' => fake()->country() . ' Republic',
            'description' => fake()->paragraph(),
        ]);
    }

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
            'name' => fake()->randomElement([
                'Deutschland',
                'Frankreich',
                'Italien',
                'Spanien',
                'Polen',
                'Niederlande',
                'Belgien',
                'Österreich',
            ]),
            'name_official' => fake()->randomElement([
                'Bundesrepublik Deutschland',
                'Französische Republik',
                'Italienische Republik',
                'Königreich Spanien',
                'Republik Polen',
                'Königreich der Niederlande',
                'Königreich Belgien',
                'Republik Österreich',
            ]),
            'description' => fake()->randomElement([
                'Ein Land in Westeuropa',
                'Ein Land in Osteuropa',
                'Ein Land in Südeuropa',
                'Ein Land in Nordeuropa',
                'Ein Land in Mitteleuropa',
            ]),
        ]);
    }
}
