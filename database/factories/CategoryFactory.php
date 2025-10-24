<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Database\Factories\Concerns\SupportsSequenceIndices;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    use SupportsSequenceIndices;

    protected $model = Category::class;

    /**
     * Track generated slugs per process so factories avoid collisions before
     * the models are persisted to the database.
     *
     * @var array<int, string>
     */
    private static array $generatedSlugs = [];

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
        $baseSlug = Str::slug($name);
        $slug = $this->generateUniqueSlug($baseSlug);

        return $this->guardForMissingColumns([
            'name' => $name,
            // Ensure slug collisions from deterministic category names do not break SQLite tests.
            'slug'            => $slug,
            'description'     => $this->generateCategoryDescription($name),
            'parent_id'       => null, // Will be set by seeder for subcategories
            'sort_order'      => $this->faker->numberBetween(0, 100),
            'is_visible'      => true,
            'is_active'       => true,
            'is_enabled'      => true,
            'seo_title'       => $name . ' - Profesionalūs sprendimai statybininkams',
            'seo_description' => 'Platus ' . strtolower($name) . ' asortimentas geriausiomis kainomis. Greitas pristatymas visoje Lietuvoje.',
        ]);
    }

    /**
     * Strip attributes that are unavailable on lightweight category tables used in isolated tests.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function guardForMissingColumns(array $attributes): array
    {
        $table = (new Category())->getTable();

        if (! Schema::hasTable($table)) {
            return $attributes;
        }

        foreach (array_keys($attributes) as $column) {
            if (! Schema::hasColumn($table, $column)) {
                unset($attributes[$column]);
            }
        }

        return $attributes;
    }

    /**
     * Generate a unique slug while tolerating missing category tables during in-memory migrations.
     */
    private function generateUniqueSlug(string $baseSlug): string
    {
        if (! Schema::hasTable('categories')) {
            return $baseSlug . '-' . Str::random(6);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            in_array($slug, self::$generatedSlugs, true)
            || Category::withoutGlobalScopes()->where('slug', $slug)->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        self::$generatedSlugs[] = $slug;

        return $slug;
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

        return $this->guardForMissingColumns(array_merge([
            'parent_id'  => null,
            'sort_order' => 0,
            'is_visible' => true,
        ], $preset));
    }

    public function sequence(...$sequence)
    {
        return parent::sequence(...$this->normaliseSequenceDefinitions($sequence));
    }
}
