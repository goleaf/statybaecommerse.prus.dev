<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
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

        $name = $this->faker->randomElement($lithuanianBrands);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->randomNumber(),
            'website' => $this->faker->boolean(70) ? 'https://'.Str::slug($name).'.lt' : null,
            'description' => $this->faker->boolean(80) ? $this->generateLithuanianDescription($name) : null,
            'is_enabled' => true,
            'seo_title' => $name.' - Profesionalūs įrankiai statybininkams',
            'seo_description' => 'Aukštos kokybės '.strtolower($name).' įrankiai ir įranga statybos darbams Lietuvoje.',
        ];
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

        return $this->faker->randomElement($descriptions);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Brand $brand): void {
            // Skip media for now - will be added manually or via admin
        });
    }
}
