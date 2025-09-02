<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'document_template_id' => DocumentTemplate::factory(),
            'title' => fake()->sentence(4),
            'content' => $this->generateSampleContent(),
            'variables' => $this->generateSampleVariables(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'format' => fake()->randomElement(['html', 'pdf']),
            'file_path' => null,
            'documentable_type' => Product::class,
            'documentable_id' => Product::factory(),
            'created_by' => User::factory(),
            'generated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    private function generateSampleContent(): string
    {
        return '<html>
<head>
    <title>Sample Document</title>
</head>
<body>
    <h1>Sample Document Content</h1>
    <p>This is a sample document generated for testing purposes.</p>
    <p>Customer: ' . fake()->name() . '</p>
    <p>Date: ' . fake()->date() . '</p>
    <p>Amount: €' . fake()->randomFloat(2, 10, 1000) . '</p>
</body>
</html>';
    }

    private function generateSampleVariables(): array
    {
        return [
            '$CUSTOMER_NAME' => fake()->name(),
            '$CUSTOMER_EMAIL' => fake()->email(),
            '$DOCUMENT_DATE' => fake()->date(),
            '$TOTAL_AMOUNT' => '€' . fake()->randomFloat(2, 10, 1000),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function pdf(): static
    {
        return $this->state([
            'format' => 'pdf',
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
        ]);
    }

    public function forProduct(): static
    {
        return $this->state([
            'documentable_type' => Product::class,
            'documentable_id' => Product::factory(),
        ]);
    }
}
