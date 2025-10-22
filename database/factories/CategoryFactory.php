<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private const PRESET_CATEGORIES = [
        'tools' => [
            'name'            => 'Elektriniai įrankiai',
            'slug'            => 'elektriniai-irankiai',
            'description'     => 'Profesionalūs elektriniai įrankiai statybos darbams.',
            'seo_title'       => 'Elektriniai įrankiai',
            'seo_description' => 'Platus elektrinių įrankių pasirinkimas profesionalams.',
        ],
        'fasteners' => [
            'name'            => 'Tvirtinimo elementai',
            'slug'            => 'tvirtinimo-elementai',
            'description'     => 'Varžtai, medsraigčiai ir kiti tvirtinimo elementai.',
            'seo_title'       => 'Tvirtinimo elementai',
            'seo_description' => 'Atraskite tvirtinimo elementus įvairiems projektams.',
        ],
        'safety' => [
            'name'            => 'Saugos priemonės',
            'slug'            => 'saugos-priemones',
            'description'     => 'Darbo saugos priemonės statyboms.',
            'seo_title'       => 'Saugos priemonės',
            'seo_description' => 'Apsauginiai drabužiai, akiniai ir kitos saugos priemonės.',
        ],
        'power-tools' => [
            'name'            => 'Smūginiai perforatoriai',
            'slug'            => 'smuginiai-perforatoriai',
            'description'     => 'Profesionalūs perforatoriai betonui ir mūrijimui.',
            'seo_title'       => 'Smūginiai perforatoriai',
            'seo_description' => 'Galingi smūginiai perforatoriai intensyviam darbui.',
        ],
        'hand-tools' => [
            'name'            => 'Rankiniai įrankiai',
            'slug'            => 'rankiniai-irankiai',
            'description'     => 'Kasdieniam darbui skirti rankiniai įrankiai.',
            'seo_title'       => 'Rankiniai įrankiai',
            'seo_description' => 'Rankinių įrankių pasirinkimas profesionalams ir mėgėjams.',
        ],
        'protective-gear' => [
            'name'            => 'Apsauginiai akiniai',
            'slug'            => 'apsauginiai-akiniai',
            'description'     => 'Apsauginiai akiniai darbui statybvietėje.',
            'seo_title'       => 'Apsauginiai akiniai',
            'seo_description' => 'Patikimi apsauginiai akiniai saugiam darbui.',
        ],
    ];

    public function definition(): array
    {
        $lithuanianCategories = [
            // Pagrindinės kategorijos
            'Elektriniai įrankiai',
            'Rankiniai įrankiai',
            'Statybinės medžiagos',
            'Saugos priemonės',
            'Matavimo įranga',
            'Tvirtinimo elementai',
            'Dažai ir lakavimo priemonės',
            'Santechnikos įranga',
            'Elektros instaliacijos',
            'Šildymo sistemos',
            'Ventiliacijos sistemos',
            'Izoliacijos medžiagos',
            'Stogo dangos',
            'Fasadų apdaila',
            'Grindų dangos',
            'Durys ir langai',
            'Laiptai ir pastoliai',
            'Sodo ir kiemo įranga',
            'Apsaugos sistemos',
            'Apšvietimo sprendimai',
        ];

        $name = $this->faker->randomElement($lithuanianCategories);
        $uniqueSuffix = $this->faker->unique()->numerify('###');

        return [
            'name' => $name,
            // Append a short unique suffix to keep database constraints happy during dense factory usage.
            'slug'            => Str::slug($name . '-' . $uniqueSuffix),
            'description'     => $this->generateCategoryDescription($name),
            'parent_id'       => null, // Will be set by seeder for subcategories
            'sort_order'      => $this->faker->numberBetween(0, 100),
            'is_visible'      => true,
            'seo_title'       => $name . ' - Profesionalūs sprendimai statybininkams',
            'seo_description' => 'Platus ' . strtolower($name) . ' asortimentas geriausiomis kainomis. Greitas pristatymas visoje Lietuvoje.',
        ];
    }

    private function generateCategoryDescription(string $categoryName): string
    {
        $descriptions = [
            "Profesionalūs {$categoryName} skirti statybos ir remonto darbams. Platus pasirinkimas patikimiausių gamintojų.",
            "Aukštos kokybės {$categoryName} tiek profesionalams, tiek namų meistrams. Konkurencingos kainos ir greitas pristatymas.",
            "Viskas, ko reikia {$categoryName} srityje. Nuo pagrindinių įrankių iki specializuotos įrangos.",
            "Patikimi {$categoryName} su garantija. Konsultacijos ir techninė pagalba įsigijus prekes.",
            "Platus {$categoryName} asortimentas visoms statybos reikmėms. Kokybė už prieinamą kainą.",
        ];

        return $this->faker->randomElement($descriptions);
    }

    public function withParent(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function tools(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('tools'));
    }

    public function fasteners(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('fasteners'));
    }

    public function safety(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('safety'));
    }

    public function powerTools(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('power-tools'));
    }

    public function handTools(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('hand-tools'));
    }

    public function protectiveGear(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('protective-gear'));
    }

    private function preset(string $key): array
    {
        $preset = self::PRESET_CATEGORIES[$key] ?? [];

        return array_merge([
            'parent_id'  => null,
            'sort_order' => 0,
            'is_visible' => true,
        ], $preset);
    }
}
