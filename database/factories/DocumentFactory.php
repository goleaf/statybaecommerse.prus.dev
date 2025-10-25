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
        $format = $this->faker->randomElement([
            Document::FORMAT_PDF,
            Document::FORMAT_HTML,
            Document::FORMAT_DOCX,
        ]);

        $status = $this->faker->randomElement([
            Document::STATUS_DRAFT,
            Document::STATUS_GENERATED,
            Document::STATUS_PUBLISHED,
            Document::STATUS_ARCHIVED,
        ]);

        return [
            'document_template_id' => DocumentTemplate::factory(), // Keep relational integrity with a valid template.
            'title' => $this->faker->sentence(3), // Human-readable heading rendered in the UI.
            'name' => $this->faker->words(3, true), // Friendly alias for search dropdowns.
            'type' => $this->faker->randomElement(['invoice', 'receipt', 'contract', 'report']), // Document classification for analytics.
            'version' => sprintf('v%s.%s', $this->faker->randomDigitNotNull(), $this->faker->randomDigit()), // Semantic version tag for auditing.
            'content' => $this->faker->randomHtml(),
            'variables' => [
                'ORDER_NUMBER' => $this->faker->unique()->numerify('ORD-#####'),
                'CUSTOMER_NAME' => $this->faker->name(),
                'ORDER_TOTAL' => '$'.$this->faker->randomFloat(2, 10, 1000),
                'ORDER_DATE' => $this->faker->date(),
                'COMPANY_NAME' => config('app.name'),
                'COMPANY_ADDRESS' => $this->faker->address(),
            ],
            'status' => $status, // Persist a lifecycle state recognised by the model helpers.
            'format' => $format, // Ensure mime metadata tracks the selected renderer.
            'file_path' => $format === Document::FORMAT_HTML ? null : 'documents/'.$this->faker->uuid().'.'.$format, // Skip file storage for inline HTML.
            'file_size' => $format === Document::FORMAT_HTML ? null : $this->faker->numberBetween(10_000, 5_000_000), // Approximate payload size in bytes.
            'mime_type' => match ($format) {
                Document::FORMAT_PDF => 'application/pdf',
                Document::FORMAT_HTML => 'text/html',
                Document::FORMAT_DOCX => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                default => 'application/octet-stream',
            },
            'is_public' => $this->faker->boolean(30), // Randomise whether the link is shareable without auth.
            'is_downloadable' => $format !== Document::FORMAT_HTML, // Inline HTML stays view-only by default.
            'access_password' => $this->faker->optional(0.25)->password(), // Allow modelling of protected documents.
            'documentable_type' => Order::class, // Default polymorphic relation to orders.
            'documentable_id' => Order::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'generated_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('+1 week', '+1 year'), // Allow testing of expiring access policies.
            'description' => $this->faker->sentence(12), // Provide marketing copy for admin listings.
            'notes' => $this->faker->optional()->paragraph(), // Internal notes for staff coordination.
        ];
    }

    /**
     * Indicate that the document is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_DRAFT,
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
            'status' => Document::STATUS_GENERATED,
            'format' => Document::FORMAT_PDF,
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
            'format' => Document::FORMAT_PDF,
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
            'format' => Document::FORMAT_PDF,
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
        ]);
    }

    /**
     * Indicate that the document is in HTML format.
     */
    public function html(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => Document::FORMAT_HTML,
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
