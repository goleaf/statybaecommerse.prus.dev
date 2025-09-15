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

    public function definition(): array
    {
        $actions = ['created', 'updated', 'deleted', 'restored', 'price_changed', 'stock_updated', 'status_changed'];
        $fields = ['name', 'price', 'stock_quantity', 'status', 'is_visible', 'description', 'categories'];
        
        $action = fake()->randomElement($actions);
        $field = fake()->randomElement($fields);
        
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'action' => $action,
            'field_name' => $field,
            'old_value' => fake()->randomElement([null, fake()->word(), fake()->numberBetween(1, 100)]),
            'new_value' => fake()->randomElement([fake()->word(), fake()->numberBetween(1, 100)]),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => [
                'product_name' => fake()->words(3, true),
                'product_sku' => fake()->bothify('PRD-####'),
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'reason' => fake()->randomElement(['market_adjustment', 'cost_increase', 'promotion', 'competitor_analysis']),
            ],
            'causer_type' => User::class,
            'causer_id' => User::factory(),
        ];
    }

    public function priceChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'price_changed',
            'field_name' => 'price',
            'old_value' => fake()->randomFloat(2, 10, 100),
            'new_value' => fake()->randomFloat(2, 10, 100),
            'description' => 'Price changed due to market conditions',
        ]);
    }

    public function stockUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'stock_updated',
            'field_name' => 'stock_quantity',
            'old_value' => fake()->numberBetween(0, 50),
            'new_value' => fake()->numberBetween(0, 100),
            'description' => 'Stock updated after inventory check',
        ]);
    }

    public function statusChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'status_changed',
            'field_name' => 'status',
            'old_value' => fake()->randomElement(['draft', 'published']),
            'new_value' => fake()->randomElement(['published', 'archived']),
            'description' => 'Product status updated',
        ]);
    }

    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'field_name' => 'product',
            'old_value' => null,
            'new_value' => [
                'name' => fake()->words(3, true),
                'sku' => fake()->bothify('PRD-####'),
                'price' => fake()->randomFloat(2, 10, 100),
            ],
            'description' => 'Product was created',
        ]);
    }
}
