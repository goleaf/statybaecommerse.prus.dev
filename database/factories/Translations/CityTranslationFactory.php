<?php declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CityTranslation>
 */
final class CityTranslationFactory extends Factory
{
    protected $model = CityTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->city();

        return [
            'city_id' => City::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => $name,
            'description' => $this->faker->sentence(),
        ];
    }

    public function forCity(City $city): static
    {
        return $this->state(fn(array $attributes) => [
            'city_id' => $city->id,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
