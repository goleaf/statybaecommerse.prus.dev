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

		$nameLt = $this->faker->randomElement($lithuanianBrands);
		$nameEn = Str::title($this->faker->company());
		$suffix = (string) $this->faker->unique()->randomNumber();

		return [
			'name' => [
				'lt' => $nameLt,
				'en' => $nameEn,
			],
			'slug' => [
				'lt' => Str::slug($nameLt.'-'.$suffix),
				'en' => Str::slug($nameEn.'-'.$suffix),
			],
			'website' => $this->faker->boolean(70) ? 'https://'.Str::slug($nameLt).'.lt' : null,
			'description' => [
				'lt' => $this->faker->boolean(80) ? $this->generateLithuanianDescription($nameLt) : null,
				'en' => $this->faker->boolean(80) ? $this->faker->paragraph() : null,
			],
			'is_enabled' => true,
			'seo_title' => [
				'lt' => $nameLt.' - Profesionalūs įrankiai statybininkams',
				'en' => $nameEn.' - Professional tools for builders',
			],
			'seo_description' => [
				'lt' => 'Aukštos kokybės '.strtolower($nameLt).' įrankiai ir įranga statybos darbams Lietuvoje.',
				'en' => 'High-quality '.strtolower($nameEn).' tools and equipment for construction work in Lithuania.',
			],
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
