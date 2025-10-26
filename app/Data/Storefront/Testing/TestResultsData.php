<?php

declare(strict_types=1);

namespace App\Data\Storefront\Testing;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents the aggregated contents of the `test-results.json` file consumed by the TestResults widget.
 */
final class TestResultsData implements Arrayable
{
    /**
     * @param array<int, array<string, mixed>> $tests
     * @param array<int, string>               $errors
     */
    public function __construct(
        public readonly string $status,
        public readonly int $totalTests,
        public readonly int $completedTests,
        public readonly int $passedTests,
        public readonly int $failedTests,
        public readonly array $tests,
        public readonly array $errors,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
    ) {
        // DTO centralizes normalization so the Livewire component can stay focused on presentation logic.
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['status'] ?? 'no_data'),
            (int) ($payload['total_tests'] ?? 0),
            (int) ($payload['completed_tests'] ?? 0),
            (int) ($payload['passed_tests'] ?? 0),
            (int) ($payload['failed_tests'] ?? 0),
            is_array($payload['tests'] ?? null) ? array_values($payload['tests']) : [],
            is_array($payload['errors'] ?? null) ? array_values($payload['errors']) : [],
            isset($payload['started_at']) ? (string) $payload['started_at'] : null,
            isset($payload['completed_at']) ? (string) $payload['completed_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status'          => $this->status,
            'total_tests'     => $this->totalTests,
            'completed_tests' => $this->completedTests,
            'passed_tests'    => $this->passedTests,
            'failed_tests'    => $this->failedTests,
            'tests'           => $this->tests,
            'errors'          => $this->errors,
            'started_at'      => $this->startedAt,
            'completed_at'    => $this->completedAt,
        ];
    }
}
