<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Export;
use App\Models\Order;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Export>
 */
final class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'resource' => \App\Filament\Resources\OrderResource::class,
            'model' => Order::class,
            'format' => ExportFormat::Csv,
            'columns' => ['number'],
            'selection' => [],
            'filters' => null,
            'status' => ExportStatus::Pending,
            'chunk_size' => (int) config('export.chunk_size', 500),
        ];
    }

    public function completed(string $path): self
    {
        return $this->state(fn () => [
            'status' => ExportStatus::Completed,
            'path' => $path,
            'available_until' => now()->addDay(),
        ]);
    }
}
