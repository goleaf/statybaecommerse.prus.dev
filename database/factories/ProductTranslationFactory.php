<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Translations\ProductTranslation>
 */
class ProductTranslationFactory extends Factory
{
    protected $model = ProductTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        
        return [
            'product_id' => Product::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'summary' => $this->faker->sentence(10),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(5),
            'seo_title' => $name . ' - ' . $this->faker->words(2, true),
            'seo_description' => $this->faker->sentence(15),
            'meta_keywords' => $this->faker->words(5),
            'alt_text' => $this->faker->sentence(3),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->randomElement([
                'Elektrinis perforatorius',
                'Kampuotasis šlifuoklis',
                'Elektrinis pjūklas',
                'Suktuvas-gręžtuvas',
                'Profesionalus plaktukas',
                'Statybinė gulsčioji',
                'Cemento mišinys',
                'Gipso plokštės',
                'Apsauginiai akiniai',
                'Darbo pirštinės',
            ]),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->randomElement([
                'Electric Drill',
                'Angle Grinder',
                'Electric Saw',
                'Screwdriver-Drill',
                'Professional Hammer',
                'Construction Level',
                'Cement Mix',
                'Gypsum Boards',
                'Safety Glasses',
                'Work Gloves',
            ]),
        ]);
    }
}
