<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductHistory>
 */
final class ProductHistoryFactory extends Factory
{
    protected $model = ProductHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted', 'restored', 'price_changed', 'stock_updated', 'status_changed', 'category_changed', 'image_changed', 'custom']),
            'field_name' => $this->faker->randomElement(['name', 'description', 'price', 'stock_quantity', 'status', 'category_id', 'image', 'meta_title', 'meta_description']),
            'old_value' => $this->faker->optional()->sentence(),
            'new_value' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => [
                'source' => $this->faker->randomElement(['admin_panel', 'api', 'import', 'migration']),
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Create a product creation history
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'field_name' => 'name',
            'old_value' => null,
            'new_value' => $this->faker->sentence(3),
            'description' => 'Product was created in the system',
        ]);
    }

    /**
     * Create a product update history
     */
    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'field_name' => $this->faker->randomElement(['name', 'description', 'price', 'stock_quantity']),
            'old_value' => $this->faker->sentence(),
            'new_value' => $this->faker->sentence(),
            'description' => 'Product was updated',
        ]);
    }

    /**
     * Create a product deletion history
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'deleted',
            'field_name' => 'status',
            'old_value' => 'active',
            'new_value' => 'deleted',
            'description' => 'Product was deleted',
        ]);
    }

    /**
     * Create a product restoration history
     */
    public function restored(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'restored',
            'field_name' => 'status',
            'old_value' => 'deleted',
            'new_value' => 'active',
            'description' => 'Product was restored',
        ]);
    }

    /**
     * Create a price change history
     */
    public function priceChanged(): static
    {
        $oldPrice = $this->faker->randomFloat(2, 10, 100);
        $newPrice = $oldPrice + $this->faker->randomFloat(2, -20, 30);

        return $this->state(fn (array $attributes) => [
            'action' => 'price_changed',
            'field_name' => 'price',
            'old_value' => $oldPrice,
            'new_value' => $newPrice,
            'description' => 'Product price was updated',
            'metadata' => [
                'price_change_percentage' => round((($newPrice - $oldPrice) / $oldPrice) * 100, 2),
                'reason' => $this->faker->randomElement(['Market adjustment', 'Promotion', 'Cost increase']),
            ],
        ]);
    }

    /**
     * Create a stock update history
     */
    public function stockUpdated(): static
    {
        $oldStock = $this->faker->numberBetween(0, 50);
        $newStock = $oldStock + $this->faker->numberBetween(-20, 100);

        return $this->state(fn (array $attributes) => [
            'action' => 'stock_updated',
            'field_name' => 'stock_quantity',
            'old_value' => $oldStock,
            'new_value' => $newStock,
            'description' => 'Stock quantity was updated',
            'metadata' => [
                'stock_change' => $newStock - $oldStock,
                'reason' => $newStock > $oldStock ? 'Restock' : 'Sale',
            ],
        ]);
    }

    /**
     * Create a status change history
     */
    public function statusChanged(): static
    {
        $statuses = ['draft', 'published', 'archived', 'pending'];
        $oldStatus = $this->faker->randomElement($statuses);
        $newStatus = $this->faker->randomElement(array_diff($statuses, [$oldStatus]));

        return $this->state(fn (array $attributes) => [
            'action' => 'status_changed',
            'field_name' => 'status',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'description' => 'Product status was changed',
            'metadata' => [
                'status_change_reason' => $this->faker->randomElement(['Administrative action', 'Content review', 'Publishing']),
            ],
        ]);
    }

    /**
     * Create a category change history
     */
    public function categoryChanged(): static
    {
        $categories = ['Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books'];
        $oldCategory = $this->faker->randomElement($categories);
        $newCategory = $this->faker->randomElement(array_diff($categories, [$oldCategory]));

        return $this->state(fn (array $attributes) => [
            'action' => 'category_changed',
            'field_name' => 'category_id',
            'old_value' => $oldCategory,
            'new_value' => $newCategory,
            'description' => 'Product category was changed',
            'metadata' => [
                'category_change_reason' => $this->faker->randomElement(['Better categorization', 'Administrative correction', 'Product reclassification']),
            ],
        ]);
    }

    /**
     * Create an image change history
     */
    public function imageChanged(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'image_changed',
            'field_name' => 'image',
            'old_value' => 'old-image-'.$this->faker->randomNumber(3).'.jpg',
            'new_value' => 'new-image-'.$this->faker->randomNumber(3).'.jpg',
            'description' => 'Product image was updated',
            'metadata' => [
                'image_change_reason' => $this->faker->randomElement(['Better quality image', 'Updated design', 'New product photo']),
            ],
        ]);
    }

    /**
     * Create a custom history
     */
    public function custom(): static
    {
        $customActions = [
            'bulk_import' => 'Product was imported via bulk import',
            'api_update' => 'Product was updated via API',
            'migration' => 'Product data was migrated',
            'sync' => 'Product was synchronized with external system',
        ];

        $action = $this->faker->randomElement(array_keys($customActions));

        return $this->state(fn (array $attributes) => [
            'action' => 'custom',
            'field_name' => 'system',
            'old_value' => null,
            'new_value' => $action,
            'description' => $customActions[$action],
            'metadata' => [
                'custom_action' => $action,
                'system_source' => 'automated',
            ],
        ]);
    }

    /**
     * Create a recent history (within last 7 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an old history (older than 30 days)
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    /**
     * Create a history for a specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Create a history for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a history with significant change
     */
    public function significant(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_name' => $this->faker->randomElement(['price', 'sale_price', 'stock_quantity', 'status', 'is_visible']),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'impact' => 'high',
                'significant_change' => true,
            ]),
        ]);
    }

    /**
     * Create a history with low impact change
     */
    public function lowImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_name' => $this->faker->randomElement(['meta_title', 'meta_description', 'tags', 'notes']),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'impact' => 'low',
                'significant_change' => false,
            ]),
        ]);
    }
}
