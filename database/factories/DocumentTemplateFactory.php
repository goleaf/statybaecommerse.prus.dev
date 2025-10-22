<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
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
            'name'        => fake()->words(3, true),
            'slug'        => fake()->unique()->slug(),
            'description' => fake()->sentence(10),
            'content'     => fake()->randomHtml(3, 5),
            'variables'   => [
                'customer_name' => 'Customer Name',
                'order_number'  => 'Order Number',
                'total_amount'  => 'Total Amount',
            ],
            'type'     => fake()->randomElement(DocumentTemplateType::cases())->value,
            'category' => fake()->randomElement(DocumentTemplateCategory::cases())->value,
            'settings' => [
                'header_enabled' => fake()->boolean(),
                'footer_enabled' => fake()->boolean(),
                'watermark'      => fake()->boolean(),
            ],
            // Default to active so admin CRUD flows and tests consistently surface the record.
            'is_active' => true,
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
            'type'     => DocumentTemplateType::Invoice->value,
            'category' => DocumentTemplateCategory::Financial->value,
        ]);
    }

    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'     => DocumentTemplateType::Receipt->value,
            'category' => DocumentTemplateCategory::Financial->value,
        ]);
    }

    public function quote(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'     => DocumentTemplateType::Quote->value,
            'category' => DocumentTemplateCategory::Marketing->value,
        ]);
    }

    public function contract(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'     => DocumentTemplateType::Contract->value,
            'category' => DocumentTemplateCategory::Legal->value,
        ]);
    }

    public function report(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'     => DocumentTemplateType::Report->value,
            'category' => DocumentTemplateCategory::Technical->value,
        ]);
    }
}
