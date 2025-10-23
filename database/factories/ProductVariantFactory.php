<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $attributes = [
            'product_id'               => Product::factory(),
            'name'                     => Str::title($this->faker->words(2, true)),
            'sku'                      => strtoupper(Str::random(12)),
            'barcode'                  => $this->faker->boolean(40) ? strtoupper(Str::random(12)) : null,
            'price'                    => $this->faker->randomFloat(2, 10, 500),
            'compare_price'            => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 500, 800) : null,
            'cost_price'               => $this->faker->randomFloat(2, 5, 300),
            'stock_quantity'           => $this->faker->numberBetween(0, 100),
            'weight'                   => $this->faker->randomFloat(3, 0.1, 10.0),
            'track_inventory'          => $this->faker->boolean(80),
            'is_default'               => $this->faker->boolean(10),
            'is_enabled'               => true,
            'attributes'               => null,
            'variant_attribute_matrix' => null,
        ];

        // Skip matrix storage when the backing schema does not expose the JSON column
        // (for example, simplified schemas defined inside isolated component tests).
        if (! Schema::hasColumn((new ProductVariant)->getTable(), 'variant_attribute_matrix')) {
            unset($attributes['variant_attribute_matrix']);
        }

        return $attributes;
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (ProductVariant $variant): void {
                if (! Schema::hasColumn($variant->getTable(), 'variant_attribute_matrix')) {
                    return;
                }

                if (! empty($variant->variant_attribute_matrix)) {
                    return;
                }

                $variant->variant_attribute_matrix = $this->generateDefaultMatrix($variant);
            })
            ->afterCreating(function (ProductVariant $variant): void {
                $this->createTranslations($variant);

                if (! Schema::hasColumn($variant->getTable(), 'variant_attribute_matrix')) {
                    return;
                }

                $matrix = $variant->variant_attribute_matrix;

                if (empty($matrix)) {
                    $matrix = $this->generateDefaultMatrix($variant);

                    if (empty($matrix)) {
                        return;
                    }

                    $variant->forceFill(['variant_attribute_matrix' => $matrix])->save();
                }

                ProductVariantAttributeMatrixService::sync($variant->fresh(), $matrix);
            });
    }

    /**
     * @return array<string, int>
     */
    private function generateDefaultMatrix(ProductVariant $variant): array
    {
        $product = $variant->product()->with(['attributes.values' => fn ($query) => $query->orderBy('sort_order')])->first();

        $attributes = $product?->attributes;

        if (blank($attributes)) {
            $attributes = Attribute::query()
                ->with(['values' => fn ($query) => $query->orderBy('sort_order')])
                ->limit(2)
                ->get();
        }

        return $attributes
            ->filter(fn ($attribute) => $attribute->values->isNotEmpty())
            ->mapWithKeys(fn ($attribute) => [
                'attribute_' . $attribute->getKey() => $attribute->values->first()->getKey(),
            ])
            ->all();
    }

    private function createTranslations(ProductVariant $variant): void
    {
        if (! method_exists($variant, 'translations')) {
            return;
        }

        $relation = $variant->translations();
        $connection = $relation->getQuery()->getConnection();
        $schema = $connection->getSchemaBuilder();
        $translationsTable = $relation->getRelated()->getTable();

        if (! $schema->hasTable($translationsTable)) {
            return;
        }

        if ($relation->exists()) {
            return;
        }

        $defaultLocale = config('app.locale', 'en');
        $locales = $this->supportedLocales();
        $faker = fake();
        $description = $faker->paragraph();

        $payload = collect($locales)->map(function (string $locale) use ($variant, $defaultLocale, $faker, $description): array {
            $name = $locale === $defaultLocale
                ? ($variant->name ?? $faker->words(2, true))
                : Str::title($faker->words(3, true));

            return [
                'locale'          => $locale,
                'name'            => $name,
                'description'     => $description,
                'seo_title'       => $faker->sentence(6),
                'seo_description' => $faker->sentence(12),
            ];
        })->values()->all();

        if ($payload !== []) {
            $variant->translations()->createMany($payload);
        }
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $locales = config('app.supported_locales', ['lt', 'en']);

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
