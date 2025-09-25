<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Database\Factories\Translations\CityTranslationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
final class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'slug' => $this->faker->unique()->slug(),
            'code' => strtoupper($this->faker->lexify('??-???')),
            'description' => $this->faker->sentence(),
            'is_capital' => $this->faker->boolean(10),
            'is_enabled' => true,
            'is_default' => false,
            'level' => $this->faker->numberBetween(0, 2),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'population' => $this->faker->numberBetween(1_000, 2_000_000),
            'postal_codes' => [$this->faker->postcode()],
            'country_id' => Country::factory(),
            'zone_id' => null,
            'region_id' => null,
            'sort_order' => $this->faker->numberBetween(1, 50),
        ];
    }

    public function capital(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_capital' => true,
        ]);
    }

    public function forCountry(Country $country): static
    {
        return $this->state(fn(array $attributes) => [
            'country_id' => $country->id,
        ]);
    }

    public function withRegion(Region $region): static
    {
        return $this->state(fn(array $attributes) => [
            'region_id' => $region->id,
            'country_id' => $region->country_id,
        ]);
    }

    public function withTranslations(array $locales = ['lt', 'en']): static
    {
        return $this->afterCreating(function (City $city) use ($locales): void {
            foreach ($locales as $locale) {
                CityTranslationFactory::new()
                    ->forCity($city)
                    ->forLocale($locale)
                    ->create();
            }
        });
    }
}
