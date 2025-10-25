<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<FailedJob>
 */
final class FailedJobFactory extends Factory
{
    /**
     * Tie the factory to the FailedJob model.
     */
    protected $model = FailedJob::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Build a realistic payload that mirrors the stored job metadata structure.
        $payload = [
            'displayName' => $this->faker->unique()->jobTitle(),
        ];

        return [
            'uuid'       => (string) Str::uuid(),
            'connection' => $this->faker->randomElement(['redis', 'database']),
            'queue'      => $this->faker->randomElement(['default', 'high', 'low']),
            'payload'    => json_encode($payload, JSON_THROW_ON_ERROR),
            'exception'  => $this->faker->sentence(),
            'failed_at'  => Carbon::now(),
        ];
    }

    /**
     * Provide a specific display name in the payload when desired.
     */
    public function withDisplayName(?string $displayName = null): self
    {
        // Allow tests to inject deterministic display names for assertions.
        $name = $displayName ?? $this->faker->unique()->jobTitle();

        return $this->state(fn (array $attributes): array => [
            'payload' => json_encode(['displayName' => $name], JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Remove the display name from the payload to simulate legacy records.
     */
    public function withoutDisplayName(): self
    {
        // Force the payload to omit the display name so fallback logic can be tested easily.
        return $this->state(fn (array $attributes): array => [
            'payload' => '{}',
        ]);
    }
}
