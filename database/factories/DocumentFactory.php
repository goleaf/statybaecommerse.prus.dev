<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
final class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'document_template_id' => DocumentTemplate::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->randomHtml(),
            'variables' => [
                'ORDER_NUMBER' => $this->faker->unique()->numerify('ORD-#####'),
                'CUSTOMER_NAME' => $this->faker->name(),
                'ORDER_TOTAL' => '$'.$this->faker->randomFloat(2, 10, 1000),
                'ORDER_DATE' => $this->faker->date(),
                'COMPANY_NAME' => config('app.name'),
                'COMPANY_ADDRESS' => $this->faker->address(),
            ],
            'status' => $this->faker->randomElement(['draft', 'generated', 'sent', 'archived']),
            'format' => $this->faker->randomElement(['pdf', 'html', 'docx']),
            'file_path' => $this->faker->optional(0.7)->filePath(),
            'documentable_type' => Order::class,
            'documentable_id' => Order::factory(),
            'created_by' => User::factory(),
            'generated_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the document is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'file_path' => null,
            'generated_at' => null,
        ]);
    }

    /**
     * Indicate that the document is generated.
     */
    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'generated',
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'generated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the document is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'generated_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
        ]);
    }

    /**
     * Indicate that the document is an invoice.
     */
    public function invoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Invoice #'.$this->faker->unique()->numerify('#####'),
            'variables' => [
                'INVOICE_NUMBER' => $this->faker->unique()->numerify('INV-#####'),
                'CUSTOMER_NAME' => $this->faker->name(),
                'INVOICE_DATE' => $this->faker->date(),
                'DUE_DATE' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
                'SUBTOTAL' => '$'.$this->faker->randomFloat(2, 100, 1000),
                'TAX_AMOUNT' => '$'.$this->faker->randomFloat(2, 10, 100),
                'TOTAL_AMOUNT' => '$'.$this->faker->randomFloat(2, 110, 1100),
            ],
        ]);
    }

    /**
     * Indicate that the document is a receipt.
     */
    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Receipt #'.$this->faker->unique()->numerify('#####'),
            'variables' => [
                'RECEIPT_NUMBER' => $this->faker->unique()->numerify('REC-#####'),
                'CUSTOMER_NAME' => $this->faker->name(),
                'PURCHASE_DATE' => $this->faker->date(),
                'PAYMENT_METHOD' => $this->faker->randomElement(['Credit Card', 'Cash', 'Bank Transfer']),
                'AMOUNT_PAID' => '$'.$this->faker->randomFloat(2, 10, 500),
            ],
        ]);
    }

    /**
     * Indicate that the document is a contract.
     */
    public function contract(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Contract #'.$this->faker->unique()->numerify('#####'),
            'variables' => [
                'CONTRACT_NUMBER' => $this->faker->unique()->numerify('CON-#####'),
                'PARTY_A' => config('app.name'),
                'PARTY_B' => $this->faker->company(),
                'CONTRACT_DATE' => $this->faker->date(),
                'EFFECTIVE_DATE' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
                'EXPIRY_DATE' => $this->faker->dateTimeBetween('+1 year', '+2 years')->format('Y-m-d'),
                'CONTRACT_VALUE' => '$'.$this->faker->randomFloat(2, 1000, 50000),
            ],
        ]);
    }

    /**
     * Indicate that the document is in PDF format.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'pdf',
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
        ]);
    }

    /**
     * Indicate that the document is in HTML format.
     */
    public function html(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'html',
            'file_path' => null,  // HTML documents are usually not stored as files
        ]);
    }

    /**
     * Indicate that the document has no file path.
     */
    public function withoutFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => null,
        ]);
    }

    /**
     * Indicate that the document has custom variables.
     */
    public function withVariables(array $variables): static
    {
        return $this->state(fn (array $attributes) => [
            'variables' => array_merge($attributes['variables'] ?? [], $variables),
        ]);
    }
}
