<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
final class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => 'product-images/'.$this->faker->uuid().'.jpg',
            'alt_text' => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Create a main product image
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/main-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'Main product image',
            'sort_order' => 1,
        ]);
    }

    /**
     * Create a gallery image
     */
    public function gallery(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/gallery-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'Gallery image',
            'sort_order' => $this->faker->numberBetween(2, 5),
        ]);
    }

    /**
     * Create a lifestyle image
     */
    public function lifestyle(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/lifestyle-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'Lifestyle image',
            'sort_order' => $this->faker->numberBetween(6, 8),
        ]);
    }

    /**
     * Create a technical image
     */
    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/technical-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'Technical specification image',
            'sort_order' => $this->faker->numberBetween(9, 10),
        ]);
    }

    /**
     * Create a thumbnail image
     */
    public function thumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/thumb-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'Thumbnail image',
            'sort_order' => 0,
        ]);
    }

    /**
     * Create a high-resolution image
     */
    public function highRes(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/high-res-'.$this->faker->uuid().'.jpg',
            'alt_text' => 'High resolution image',
            'sort_order' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create an image without alt text
     */
    public function withoutAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => null,
        ]);
    }

    /**
     * Create an image with long alt text
     */
    public function withLongAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Create an image with short alt text
     */
    public function withShortAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => $this->faker->word(),
        ]);
    }

    /**
     * Create an image for a specific product
     */
    public function forProduct(Product $product): static
    {
        $productSlug = strtolower(str_replace(' ', '-', $product->name));
        $productSlug = preg_replace('/[^a-z0-9\-]/', '', $productSlug);

        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'path' => "product-images/{$productSlug}/".$this->faker->uuid().'.jpg',
        ]);
    }

    /**
     * Create multiple images for the same product
     */
    public function forProductWithCount(Product $product, int $count = 3): static
    {
        $productSlug = strtolower(str_replace(' ', '-', $product->name));
        $productSlug = preg_replace('/[^a-z0-9\-]/', '', $productSlug);

        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'path' => "product-images/{$productSlug}/".$this->faker->uuid().'.jpg',
        ])->count($count);
    }

    /**
     * Create an image with specific sort order
     */
    public function withSortOrder(int $sortOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Create an image with specific path
     */
    public function withPath(string $path): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => $path,
        ]);
    }

    /**
     * Create an image with specific alt text
     */
    public function withAltText(string $altText): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => $altText,
        ]);
    }

    /**
     * Create an image with different file extensions
     */
    public function withExtension(string $extension): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/'.$this->faker->uuid().'.'.$extension,
        ]);
    }

    /**
     * Create a JPEG image
     */
    public function jpeg(): static
    {
        return $this->withExtension('jpg');
    }

    /**
     * Create a PNG image
     */
    public function png(): static
    {
        return $this->withExtension('png');
    }

    /**
     * Create a WebP image
     */
    public function webp(): static
    {
        return $this->withExtension('webp');
    }

    /**
     * Create an image in a specific directory
     */
    public function inDirectory(string $directory): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => $directory.'/'.$this->faker->uuid().'.jpg',
        ]);
    }

    /**
     * Create an image with specific dimensions in filename
     */
    public function withDimensions(int $width, int $height): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => 'product-images/'.$this->faker->uuid()."_{$width}x{$height}.jpg",
            'alt_text' => "Product image {$width}x{$height}",
        ]);
    }

    /**
     * Create an image for different product categories
     */
    public function forCategory(string $category): static
    {
        $categorySlug = strtolower(str_replace(' ', '-', $category));

        return $this->state(fn (array $attributes) => [
            'path' => "product-images/{$categorySlug}/".$this->faker->uuid().'.jpg',
            'alt_text' => "{$category} product image",
        ]);
    }
}
