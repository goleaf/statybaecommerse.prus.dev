<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
final class AttributeValueFactory extends Factory
{
    /**
     * Maintain a simple counter so generated attribute values remain unique across seeding runs.
     */
    private static int $valueSequence = 1;

    protected $model = AttributeValue::class;

    public function definition(): array
    {
        $sequence = self::$valueSequence++;

        // Append a deterministic sequence to the colour name to avoid Faker's limited unique pool exhausting during heavy seeders.
        $value = sprintf('%s %s', $this->faker->colorName(), $sequence);

        return [
            'attribute_id'         => fn () => \App\Models\Attribute::factory(),
            'value'                => $value,
            'slug'                 => str($value)->slug()->toString(),
            'attribute_value_type' => 'text',
            'valueable_type'       => null,
            'valueable_id'         => null,
            'color_code'           => $this->faker->boolean(40) ? $this->faker->hexColor() : null,
            'sort_order'           => $this->faker->numberBetween(0, 50),
            'is_enabled'           => true,
            'is_active'            => true,
            'is_default'           => false,
            'is_searchable'        => false,
            'display_value'        => str($value)->headline()->toString(),
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function withColor(?string $colorCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'color_code' => $colorCode ?? $this->faker->hexColor(),
        ]);
    }

    public function colorValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value'      => $this->faker->colorName(),
            'color_code' => $this->faker->hexColor(),
        ]);
    }

    public function sizeValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
        ]);
    }

    public function materialValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather']),
        ]);
    }

    public function defaultOption(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (AttributeValue $attributeValue): void {
            $this->createTranslations($attributeValue);
        });
    }

    private function createTranslations(AttributeValue $attributeValue): void
    {
        if (! method_exists($attributeValue, 'translations')) {
            return;
        }

        $relation = $attributeValue->translations();
        $connection = $relation->getQuery()->getConnection();
        $schema = $connection->getSchemaBuilder();
        $translationsTable = $relation->getRelated()->getTable();

        if (! $schema->hasTable($translationsTable) || $relation->exists()) {
            return;
        }

        if (! $schema->hasColumn($translationsTable, 'value')) {
            return;
        }

        if (! $schema->hasColumn($translationsTable, 'description')) {
            return;
        }

        $defaultLocale = config('app.locale', 'en');
        $locales = $this->supportedLocales();

        $translations = collect($locales)->map(function (string $locale) use ($attributeValue, $defaultLocale): array {
            $value = $attributeValue->value;

            return [
                'locale'      => $locale,
                'value'       => $locale === $defaultLocale ? $value : "{$value} ({$locale})",
                'description' => $locale === $defaultLocale ? ($attributeValue->description ?? null) : null,
            ];
        })->values()->all();

        if ($translations !== []) {
            $attributeValue->translations()->createMany($translations);
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
