<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * @extends Factory<\App\Models\Translations\ProductTranslation>
 */
class ProductTranslationFactory extends Factory
{
    protected $model = ProductTranslation::class;

    public function create($attributes = [], ?Model $parent = null)
    {
        try {
            return parent::create($attributes, $parent);
        } catch (QueryException $exception) {
            // When tests override the locale for a specific product we may hit the unique
            // constraint on (product_id, locale). In that scenario we update the existing
            // translation instead of throwing so fixtures can intentionally mutate the
            // persisted HTML without having to worry about the factory defaults.
            if ($attributes === []
                || ! $this->isDuplicateProductLocaleViolation($exception)
            ) {
                throw $exception;
            }

            $model = $this->state($attributes)->make([], $parent);

            if (! $model instanceof Model) {
                throw $exception;
            }

            $payload = $model->getAttributes();
            $productId = $payload['product_id'] ?? null;
            $locale = $payload['locale'] ?? null;

            if (! is_int($productId) || ! is_string($locale)) {
                throw $exception;
            }

            $existing = ProductTranslation::query()
                ->where('product_id', $productId)
                ->where('locale', $locale)
                ->first();

            if ($existing === null) {
                throw $exception;
            }

            $existing->fill($payload)->save();

            return $existing;
        }
    }

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
            'seo_title' => $name.' - '.$this->faker->words(2, true),
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

    private function isDuplicateProductLocaleViolation(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        if ($message === null) {
            return false;
        }

        return str_contains($message, 'product_translations.product_id, product_translations.locale');
    }
}
