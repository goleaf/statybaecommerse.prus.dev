<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Translations\ProductVariantTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Translations\ProductVariantTranslation>
 */
final class ProductVariantTranslationFactory extends Factory
{
    protected $model = ProductVariantTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'product_variant_id' => ProductVariant::factory(),
            'locale'             => $this->faker->randomElement($this->supportedLocales()),
            'name'               => $name,
            'description'        => $this->faker->paragraph(),
            'seo_title'          => ucfirst($name) . ' – ' . $this->faker->words(2, true),
            'seo_description'    => $this->faker->sentence(12),
        ];
    }

    public function forVariant(int $variantId): static
    {
        return $this->state(fn (): array => [
            'product_variant_id' => $variantId,
        ]);
    }

    public function locale(string $locale): static
    {
        return $this->state(fn (): array => [
            'locale' => $locale,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $locales = config('app.supported_locales', 'lt,en');

        if (is_string($locales)) {
            $locales = explode(',', $locales);
        }

        return collect($locales)
            ->map(static fn ($locale): string => trim((string) $locale))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
