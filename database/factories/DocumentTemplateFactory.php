<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    protected $model = DocumentTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(10),
            'content' => fake()->randomHtml(3, 5),
            'variables' => [
                'customer_name' => 'Customer Name',
                'order_number' => 'Order Number',
                'total_amount' => 'Total Amount',
            ],
            'type' => fake()->randomElement(['invoice', 'receipt', 'quote', 'contract', 'report']),
            'category' => fake()->randomElement(['financial', 'legal', 'marketing', 'operational']),
            'settings' => [
                'header_enabled' => fake()->boolean(),
                'footer_enabled' => fake()->boolean(),
                'watermark' => fake()->boolean(),
            ],
            'is_active' => fake()->boolean(80),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function invoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'invoice',
            'category' => 'financial',
        ]);
    }

    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'receipt',
            'category' => 'financial',
        ]);
    }

    public function quote(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quote',
            'category' => 'marketing',
        ]);
    }

    public function contract(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'contract',
            'category' => 'legal',
        ]);
    }

    public function report(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'report',
            'category' => 'operational',
        ]);
    }
}
