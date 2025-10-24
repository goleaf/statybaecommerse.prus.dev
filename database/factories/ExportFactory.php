<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Export>
 */
final class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->words(3, true) . ' Export',
            'format' => $this->faker->randomElement(['csv', 'xlsx', 'json', 'xml']),
            'status' => ExportStatus::Queued,
            'exportable_type' => $this->faker->randomElement(['Product', 'Order', 'Customer', 'User']),
            'columns' => ['id', 'name', 'created_at'],
            'exportable_options' => [
                'filters' => $this->faker->randomElements(['active', 'verified', 'premium'], 2),
                'date_range' => [
                    'from' => $this->faker->dateTimeBetween('-1 month')->format('Y-m-d'),
                    'to' => now()->format('Y-m-d'),
                ],
            ],
            'total_rows' => $this->faker->numberBetween(100, 10000),
            'processed_rows' => 0,
            'artifact_disk' => config('export.disk', 'public'),
            'artifact_path' => null,
            'artifact_filename' => null,
            'requested_at' => now(),
            'completed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
            'requested_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the export is queued.
     */
    public function queued(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => ExportStatus::Queued,
            'completed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
            'artifact_path' => null,
            'artifact_filename' => null,
            'processed_rows' => 0,
        ]);
    }

    /**
     * Indicate that the export is processing.
     */
    public function processing(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => ExportStatus::Processing,
            'processed_rows' => (int) ($attributes['total_rows'] * 0.5),
            'completed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Indicate that the export is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes): array {
            $filename = Str::slug($attributes['name']) . '.' . $attributes['format'];

            return [
                'status' => ExportStatus::Completed,
                'processed_rows' => $attributes['total_rows'],
                'completed_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
                'artifact_path' => 'exports/' . $filename,
                'artifact_filename' => $filename,
            ];
        });
    }

    /**
     * Indicate that the export has failed.
     */
    public function failed(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => ExportStatus::Failed,
            'failed_at' => now(),
            'failure_reason' => $this->faker->randomElement([
                'Insufficient memory',
                'Database timeout',
                'Invalid data format',
                'Permission denied',
                'Disk quota exceeded',
            ]),
            'completed_at' => null,
            'artifact_path' => null,
            'artifact_filename' => null,
        ]);
    }

    /**
     * Indicate that the export is for products.
     */
    public function forProducts(): static
    {
        return $this->state(fn(array $attributes): array => [
            'name' => 'Product Export',
            'exportable_type' => 'Product',
            'columns' => ['id', 'name', 'sku', 'price', 'stock', 'created_at'],
        ]);
    }

    /**
     * Indicate that the export is for orders.
     */
    public function forOrders(): static
    {
        return $this->state(fn(array $attributes): array => [
            'name' => 'Order Export',
            'exportable_type' => 'Order',
            'columns' => ['id', 'number', 'total', 'status', 'customer_email', 'created_at'],
        ]);
    }

    /**
     * Indicate that the export is for customers.
     */
    public function forCustomers(): static
    {
        return $this->state(fn(array $attributes): array => [
            'name' => 'Customer Export',
            'exportable_type' => 'Customer',
            'columns' => ['id', 'name', 'email', 'phone', 'city', 'created_at'],
        ]);
    }
}
