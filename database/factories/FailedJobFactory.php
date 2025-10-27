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
     * Provide a monotonic counter for generating deterministic display names without Faker formatters.
     */
    private static int $displayNameSequence = 1;

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
            'displayName' => $this->generateJobDisplayName(),
        ];

        return [
            'uuid'       => (string) Str::uuid(),
            'connection' => 'redis',
            'queue'      => 'default',
            'payload'    => json_encode($payload, JSON_THROW_ON_ERROR),
            'exception'  => sprintf('Example failure message %d', self::$displayNameSequence),
            'failed_at'  => Carbon::now(),
        ];
    }

    /**
     * Provide a specific display name in the payload when desired.
     */
    public function withDisplayName(?string $displayName = null): self
    {
        // Allow tests to inject deterministic display names for assertions.
        $name = $displayName ?? $this->generateJobDisplayName();

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

    /**
     * Produce a plausible job class name for the payload display name field.
     */
    private function generateJobDisplayName(): string
    {
        $sequence = self::$displayNameSequence++;

        return sprintf('App\\Jobs\\ExampleJob%d', $sequence);
    }
}
