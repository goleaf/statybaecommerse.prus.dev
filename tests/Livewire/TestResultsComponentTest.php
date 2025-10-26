<?php

declare(strict_types=1);

use App\Livewire\TestResults;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->resultsPath = storage_path('app/test-results.json');

    File::ensureDirectoryExists(dirname($this->resultsPath));

    if (File::exists($this->resultsPath)) {
        File::delete($this->resultsPath);
    }
});

afterEach(function (): void {
    if (isset($this->resultsPath) && File::exists($this->resultsPath)) {
        File::delete($this->resultsPath);
    }
});

it('exposes a no data state when the results file is missing', function (): void {
    /** @var TestResults $component */
    $component = app(TestResults::class);
    $component->mount();

    expect($component->isRunning)->toBeFalse();
    expect($component->progress)->toBe(0);
    expect($component->resultsData->status)->toBe('no_data');
    expect($component->getResultsProperty())->toBe($component->resultsData->toArray());
});

it('calculates progress from stored results and refreshes after updates', function (): void {
    $initialPayload = [
        'status'          => 'running',
        'total_tests'     => 10,
        'completed_tests' => 4,
        'passed_tests'    => 4,
        'failed_tests'    => 0,
        'tests'           => [],
        'errors'          => [],
        'started_at'      => '2025-10-01T10:00:00Z',
        'completed_at'    => null,
    ];

    File::put(
        $this->resultsPath,
        json_encode($initialPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    /** @var TestResults $component */
    $component = app(TestResults::class);
    $component->mount();

    expect($component->isRunning)->toBeTrue();
    expect($component->progress)->toBe(40);
    expect($component->resultsData->totalTests)->toBe(10);

    $updatedPayload = [
        'status'          => 'completed',
        'total_tests'     => 10,
        'completed_tests' => 10,
        'passed_tests'    => 9,
        'failed_tests'    => 1,
        'tests'           => [],
        'errors'          => ['1 failing test'],
        'started_at'      => '2025-10-01T10:00:00Z',
        'completed_at'    => '2025-10-01T10:05:00Z',
    ];

    File::put(
        $this->resultsPath,
        json_encode($updatedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $component->refreshResults();

    expect($component->isRunning)->toBeFalse();
    expect($component->progress)->toBe(100);
    expect($component->resultsData->failedTests)->toBe(1);
    expect($component->resultsData->errors)->toBe(['1 failing test']);
});

it('handles invalid json payloads gracefully', function (): void {
    File::put($this->resultsPath, '{invalid json');

    /** @var TestResults $component */
    $component = app(TestResults::class);
    $component->mount();

    expect($component->resultsData->status)->toBe('no_data');
    expect($component->progress)->toBe(0);
});
