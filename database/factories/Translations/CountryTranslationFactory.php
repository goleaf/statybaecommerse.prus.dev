<?php

declare(strict_types=1);

namespace Database\Factories\Translations;

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
            'locale' => $this->faker->randomElement(['lt', 'en', 'de', 'fr', 'es']),
            'name' => $this->faker->country(),
            'name_official' => $this->faker->optional(0.7)->country(),
            'description' => $this->faker->optional(0.6)->paragraph(),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->randomElement(['Lietuva', 'Latvija', 'Estija', 'Lenkija', 'Vokietija', 'Prancūzija', 'Ispanija', 'Italija', 'Portugalija', 'Nyderlandai']),
            'name_official' => $this->faker->optional(0.7)->randomElement(['Lietuvos Respublika', 'Latvijos Respublika', 'Estijos Respublika', 'Lenkijos Respublika', 'Vokietijos Federacinė Respublika', 'Prancūzijos Respublika', 'Ispanijos Karalystė', 'Italijos Respublika', 'Portugalijos Respublika', 'Nyderlandų Karalystė']),
            'description' => $this->faker->optional(0.6)->randomElement([
                'Šalis Europoje',
                'Šiaurės Europos šalis',
                'Rytų Europos šalis',
                'Vakarų Europos šalis',
                'Pietų Europos šalis',
                'Centrinės Europos šalis',
                'Baltijos šalis',
                'Skandinavijos šalis',
                'Balkanų šalis',
                'Beneliukso šalis',
            ]),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->country(),
            'name_official' => $this->faker->optional(0.7)->country(),
            'description' => $this->faker->optional(0.6)->randomElement([
                'A country in Europe',
                'A Northern European country',
                'An Eastern European country',
                'A Western European country',
                'A Southern European country',
                'A Central European country',
                'A Baltic country',
                'A Scandinavian country',
                'A Balkan country',
                'A Benelux country',
            ]),
        ]);
    }

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
            'name' => $this->faker->randomElement(['Litauen', 'Lettland', 'Estland', 'Polen', 'Deutschland', 'Frankreich', 'Spanien', 'Italien', 'Portugal', 'Niederlande']),
            'name_official' => $this->faker->optional(0.7)->randomElement(['Republik Litauen', 'Republik Lettland', 'Republik Estland', 'Republik Polen', 'Bundesrepublik Deutschland', 'Französische Republik', 'Königreich Spanien', 'Italienische Republik', 'Portugiesische Republik', 'Königreich der Niederlande']),
            'description' => $this->faker->optional(0.6)->randomElement([
                'Ein Land in Europa',
                'Ein nordeuropäisches Land',
                'Ein osteuropäisches Land',
                'Ein westeuropäisches Land',
                'Ein südeuropäisches Land',
                'Ein mitteleuropäisches Land',
                'Ein baltisches Land',
                'Ein skandinavisches Land',
                'Ein Balkanland',
                'Ein Benelux-Land',
            ]),
        ]);
    }

    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'fr',
            'name' => $this->faker->randomElement(['Lituanie', 'Lettonie', 'Estonie', 'Pologne', 'Allemagne', 'France', 'Espagne', 'Italie', 'Portugal', 'Pays-Bas']),
            'name_official' => $this->faker->optional(0.7)->randomElement(['République de Lituanie', 'République de Lettonie', 'République d\'Estonie', 'République de Pologne', 'République fédérale d\'Allemagne', 'République française', 'Royaume d\'Espagne', 'République italienne', 'République portugaise', 'Royaume des Pays-Bas']),
            'description' => $this->faker->optional(0.6)->randomElement([
                'Un pays en Europe',
                'Un pays d\'Europe du Nord',
                'Un pays d\'Europe de l\'Est',
                'Un pays d\'Europe de l\'Ouest',
                'Un pays d\'Europe du Sud',
                'Un pays d\'Europe centrale',
                'Un pays baltique',
                'Un pays scandinave',
                'Un pays balkanique',
                'Un pays du Benelux',
            ]),
        ]);
    }

    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'es',
            'name' => $this->faker->randomElement(['Lituania', 'Letonia', 'Estonia', 'Polonia', 'Alemania', 'Francia', 'España', 'Italia', 'Portugal', 'Países Bajos']),
            'name_official' => $this->faker->optional(0.7)->randomElement(['República de Lituania', 'República de Letonia', 'República de Estonia', 'República de Polonia', 'República Federal de Alemania', 'República Francesa', 'Reino de España', 'República Italiana', 'República Portuguesa', 'Reino de los Países Bajos']),
            'description' => $this->faker->optional(0.6)->randomElement([
                'Un país en Europa',
                'Un país del norte de Europa',
                'Un país del este de Europa',
                'Un país del oeste de Europa',
                'Un país del sur de Europa',
                'Un país de Europa central',
                'Un país báltico',
                'Un país escandinavo',
                'Un país balcánico',
                'Un país del Benelux',
            ]),
        ]);
    }
}
