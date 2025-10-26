<?php

declare(strict_types=1);

namespace App\Data\TestRunner;

use Illuminate\Support\Arr;

/**
 * TestResultData
 *
 * Immutable data transport for the aggregated test runner summary.
 */
final class TestResultData
{
    /**
     * @param array<int, array<string, mixed>> $tests
     * @param list<string>                     $errors
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
        // Keep payload serialisable and documented for Livewire consumers.
    }

    /**
     * Build an instance from a mixed array payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            status: (string) Arr::get($payload, 'status', 'no_data'),
            totalTests: (int) Arr::get($payload, 'total_tests', 0),
            completedTests: (int) Arr::get($payload, 'completed_tests', 0),
            passedTests: (int) Arr::get($payload, 'passed_tests', 0),
            failedTests: (int) Arr::get($payload, 'failed_tests', 0),
            tests: array_values(array_map(
                static fn ($test): array => is_array($test) ? $test : [],
                (array) Arr::get($payload, 'tests', [])
            )),
            errors: array_values(array_map(
                static fn ($error): string => (string) $error,
                (array) Arr::get($payload, 'errors', [])
            )),
            startedAt: Arr::get($payload, 'started_at'),
            completedAt: Arr::get($payload, 'completed_at'),
        );
    }

    /**
     * Convert the payload into an array for Blade rendering.
     *
     * @return array{status:string,total_tests:int,completed_tests:int,passed_tests:int,failed_tests:int,tests:array<int, array<string, mixed>>,errors:list<string>,started_at:?string,completed_at:?string}
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
