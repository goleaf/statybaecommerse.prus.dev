<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    private const PRESET_BRANDS = [
        'makita' => [
            'name'            => 'Makita',
            'slug'            => 'makita',
            'description'     => 'Makita profesionalūs statybos įrankiai ir sprendimai.',
            'website'         => 'https://www.makita.lt',
            'is_enabled'      => true,
            'is_featured'     => true,
            'seo_title'       => 'Makita - Profesionalūs įrankiai statyboms',
            'seo_description' => 'Atraskite Makita elektrinius ir rankinius įrankius statybos projektams Lietuvoje.',
        ],
        'bosch' => [
            'name'            => 'Bosch',
            'slug'            => 'bosch',
            'description'     => 'Bosch profesionalūs įrankiai ir įranga statybos darbams.',
            'website'         => 'https://www.bosch-professional.com/lt/lt/',
            'is_enabled'      => true,
            'is_featured'     => true,
            'seo_title'       => 'Bosch - Kokybiški profesionalūs įrankiai',
            'seo_description' => 'Bosch Professional įrankiai meistrams ir statybų profesionalams.',
        ],
        'dewalt' => [
            'name'            => 'DeWalt',
            'slug'            => 'dewalt',
            'description'     => 'DeWalt aukštos kokybės statybiniai įrankiai ir priedai.',
            'website'         => 'https://www.dewalt.eu/lt-lt',
            'is_enabled'      => true,
            'is_featured'     => true,
            'seo_title'       => 'DeWalt - Patikimi įrankiai statyboms',
            'seo_description' => 'DeWalt statybiniai įrankiai intensyviam profesionalų naudojimui.',
        ],
    ];

    public function definition(): array
    {
        $brand = new Brand();
        $connection = $brand->newQuery()->getConnection();
        $schema = $connection->getSchemaBuilder();
        $table = $brand->getTable();

        $hasTable = $schema->hasTable($table);
        $hasIsVisible = $hasTable && $schema->hasColumn($table, 'is_visible');
        $hasIsActive = $hasTable && $schema->hasColumn($table, 'is_active');

        $lithuanianBrands = [
            'Makita Tools LT',
            'Bosch Lietuva',
            'DeWalt Baltics',
            'Hilti Lithuania',
            'Festool Baltic',
            'Milwaukee Tools LT',
            'Metabo Lithuania',
            'Ryobi Baltics',
            'Black & Decker LT',
            'Stanley Tools Lithuania',
            'Kärcher Lietuva',
            'Husqvarna Lithuania',
            'STIHL Baltic',
            'Würth Lietuva',
            'Fischer Baltic',
            'Knauf Lithuania',
            'Rockwool Baltics',
            'URSA Insulation LT',
            'Isover Lithuania',
            'Weber Lietuva',
            'Mapei Baltic',
            'Ceresit Lithuania',
            'Henkel Baltics',
            'Sika Lietuva',
            'Tremco Baltic',
        ];

        $name = Arr::random($lithuanianBrands);

        $attributes = [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(),
            'website'     => $this->faker->boolean(70) ? 'https://' . Str::slug($name) . '.lt' : null,
            'description' => $this->faker->boolean(80) ? $this->generateLithuanianDescription($name) : null,
            // Keep generated brands visible/active so API queries honouring the scoped
            // filters can retrieve them during contract verification.
            'is_enabled'      => true,
            'seo_title'       => $name . ' - Profesionalūs įrankiai statybininkams',
            'seo_description' => 'Aukštos kokybės ' . strtolower($name) . ' įrankiai ir įranga statybos darbams Lietuvoje.',
        ];

        if ($hasIsVisible) {
            $attributes['is_visible'] = true;
        }

        if ($hasIsActive) {
            $attributes['is_active'] = true;
        }

        return $attributes;
    }

    private function generateLithuanianDescription(string $brandName): string
    {
        $descriptions = [
            "Profesionalūs statybos įrankiai ir įranga nuo {$brandName}. Patikimi sprendimai statybininkams.",
            "Aukštos kokybės {$brandName} gaminiai statybos ir remonto darbams. Garantuota kokybė.",
            "Patikimi {$brandName} įrankiai profesionaliems statybininkams Lietuvoje.",
            "Inovatyvūs {$brandName} sprendimai statybos pramonei. Efektyvumas ir patikimumas.",
            "Pilna {$brandName} įrankių ir įrangos gama namų statybai ir remontui.",
        ];

        return Arr::random($descriptions);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Brand $brand): void {
            // Skip media for now - will be added manually or via admin
        });
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_enabled'  => true,
        ]);
    }

    public function makita(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('makita'));
    }

    public function bosch(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('bosch'));
    }

    public function dewalt(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('dewalt'));
    }

    private function preset(string $key): array
    {
        $preset = self::PRESET_BRANDS[$key] ?? [];

        return array_merge([
            'is_enabled'  => true,
            'is_featured' => false,
        ], $preset);
    }
}
