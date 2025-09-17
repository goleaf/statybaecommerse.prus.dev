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

		$nameLt = $this->faker->unique()->randomElement($lithuanianCategories);
		$nameEn = Str::title($this->faker->words(2, true));
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
			'description' => [
				'lt' => $this->generateCategoryDescription($nameLt),
				'en' => $this->faker->sentence(12),
			],
			'parent_id' => null, // Will be set by seeder for subcategories
			'sort_order' => $this->faker->numberBetween(0, 100),
			'is_visible' => true,
			'seo_title' => [
				'lt' => $nameLt.' - Profesionalūs sprendimai statybininkams',
				'en' => $nameEn.' - Professional solutions for builders',
			],
			'seo_description' => [
				'lt' => 'Platus '.strtolower($nameLt).' asortimentas geriausiomis kainomis. Greitas pristatymas visoje Lietuvoje.',
				'en' => 'Wide range of '.strtolower($nameEn).' at the best prices. Fast delivery across Lithuania.',
			],
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
}
