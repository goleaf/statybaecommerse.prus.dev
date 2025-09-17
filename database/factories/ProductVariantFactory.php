<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $nameLt = Str::title($this->faker->words(2, true));
        $nameEn = Str::title($this->faker->words(2, true));
        $seoTitleLt = $nameLt . ' – variantas';
        $seoTitleEn = $nameEn . ' – variant';
        $seoDescLt = $this->faker->sentence(8) . ' – kokybiškas pasirinkimas.';
        $seoDescEn = $this->faker->sentence(8) . ' – high quality choice.';

        return [
            'product_id' => fn () => \App\Models\Product::factory()->create()->id,
            'name' => Str::title($this->faker->words(2, true)),
            'variant_name_lt' => $nameLt,
            'variant_name_en' => $nameEn,
            'description_lt' => $this->faker->paragraphs(2, true),
            'description_en' => $this->faker->paragraphs(2, true),
            'seo_title_lt' => $seoTitleLt,
            'seo_title_en' => $seoTitleEn,
            'seo_description_lt' => $seoDescLt,
            'seo_description_en' => $seoDescEn,
            'sku' => strtoupper(Str::random(12)),
            'barcode' => $this->faker->boolean(40) ? strtoupper(Str::random(12)) : null,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'compare_price' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 10, 800) : null,
            'cost_price' => $this->faker->randomFloat(2, 5, 300),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->randomFloat(3, 0.1, 10.0),
            'track_inventory' => $this->faker->boolean(80),
            'is_default' => $this->faker->boolean(10),
            'is_enabled' => true,
            'attributes' => null,
            'status' => 'active',
        ];
    }
}
