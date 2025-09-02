<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'type' => 'simple',
            'name' => Str::title($name),
            'slug' => Str::slug($name . '-' . $this->faker->unique()->randomNumber()),
            'sku' => strtoupper(Str::random(10)),
            'description' => $this->faker->boolean(80) ? '<p>' . $this->faker->paragraphs(3, true) . '</p>' : null,
            'short_description' => $this->faker->boolean(70) ? $this->faker->sentence(12) : null,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'sale_price' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 5, 400) : null,
            'brand_id' => null,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'low_stock_threshold' => $this->faker->numberBetween(5, 20),
            'weight' => $this->faker->randomFloat(2, 0.1, 5.0),
            'length' => $this->faker->randomFloat(2, 1, 50),
            'width' => $this->faker->randomFloat(2, 1, 50),
            'height' => $this->faker->randomFloat(2, 1, 50),
            'is_visible' => true,
            'is_featured' => $this->faker->boolean(10),
            'manage_stock' => $this->faker->boolean(80),
            'status' => 'published',
            'seo_title' => $this->faker->boolean(30) ? $this->faker->sentence(6) : null,
            'seo_description' => $this->faker->boolean(30) ? $this->faker->sentence(12) : null,
            'published_at' => $this->faker->dateTimeBetween('-10 days', '+10 days'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $paths = ['demo/tshirt.jpg', 'demo/product.jpg'];
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $product
                        ->addMedia(Storage::disk('public')->path($path))
                        ->toMediaCollection('products');
                    break;
                }
            }
            // Fallback to generated gradient if no demo image attached
            if ($product->getMedia('products')->isEmpty()) {
                try {
                    /** @var GradientImageService $generator */
                    $generator = app(GradientImageService::class);
                    $tmp = $generator->generateGradientPng(800, 800);
                    $product
                        ->addMedia($tmp)
                        ->withCustomProperties(['placeholder' => true])
                        ->preservingOriginal()
                        ->toMediaCollection('products');
                } catch (\Throwable $e) {
                    // swallow silently in factories
                }
            }
        });
    }
}
