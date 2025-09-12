<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeoData>
 */
final class SeoDataFactory extends Factory
{
    protected $model = SeoData::class;

    public function definition(): array
    {
        $locales = ['lt', 'en'];
        $locale = fake()->randomElement($locales);

        $seoableTypes = [Product::class, Category::class, Brand::class];
        $seoableType = fake()->randomElement($seoableTypes);

        return [
            'seoable_type' => $seoableType,
            'seoable_id' => match ($seoableType) {
                Product::class => Product::factory(),
                Category::class => Category::factory(),
                Brand::class => Brand::factory(),
                default => 1,
            },
            'locale' => $locale,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(2),
            'keywords' => implode(', ', fake()->words(5)),
            'canonical_url' => fake()->url(),
            'meta_tags' => [
                'og:type' => fake()->randomElement(['website', 'product', 'article']),
                'og:site_name' => fake()->company(),
                'twitter:card' => fake()->randomElement(['summary', 'summary_large_image']),
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => fake()->randomElement(['Product', 'Article', 'WebPage', 'Organization']),
                'name' => fake()->sentence(3),
                'description' => fake()->paragraph(1),
            ],
            'no_index' => fake()->boolean(10), // 10% chance of no_index
            'no_follow' => fake()->boolean(5), // 5% chance of no_follow
        ];
    }

    public function forProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => Product::class,
            'seoable_id' => Product::factory(),
            'meta_tags' => [
                'og:type' => 'product',
                'product:price:amount' => fake()->randomFloat(2, 10, 1000),
                'product:price:currency' => 'EUR',
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => fake()->sentence(3),
                'description' => fake()->paragraph(1),
                'sku' => fake()->unique()->ean13(),
                'brand' => fake()->company(),
            ],
        ]);
    }

    public function forCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => Category::class,
            'seoable_id' => Category::factory(),
            'meta_tags' => [
                'og:type' => 'website',
                'og:site_name' => fake()->company(),
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => fake()->sentence(2),
                'description' => fake()->paragraph(1),
            ],
        ]);
    }

    public function forBrand(): static
    {
        return $this->state(fn (array $attributes) => [
            'seoable_type' => Brand::class,
            'seoable_id' => Brand::factory(),
            'meta_tags' => [
                'og:type' => 'website',
                'og:site_name' => fake()->company(),
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Brand',
                'name' => fake()->company(),
                'description' => fake()->paragraph(1),
            ],
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'title' => fake('lt_LT')->sentence(6),
            'description' => fake('lt_LT')->paragraph(2),
            'keywords' => implode(', ', fake('lt_LT')->words(5)),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'title' => fake('en_US')->sentence(6),
            'description' => fake('en_US')->paragraph(2),
            'keywords' => implode(', ', fake('en_US')->words(5)),
        ]);
    }

    public function withNoIndex(): static
    {
        return $this->state(fn (array $attributes) => [
            'no_index' => true,
        ]);
    }

    public function withNoFollow(): static
    {
        return $this->state(fn (array $attributes) => [
            'no_follow' => true,
        ]);
    }

    public function withCustomMetaTags(array $metaTags): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_tags' => array_merge($attributes['meta_tags'] ?? [], $metaTags),
        ]);
    }

    public function withStructuredData(array $structuredData): static
    {
        return $this->state(fn (array $attributes) => [
            'structured_data' => array_merge($attributes['structured_data'] ?? [], $structuredData),
        ]);
    }
}
