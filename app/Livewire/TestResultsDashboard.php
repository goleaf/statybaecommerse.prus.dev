<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * TestResultsDashboard Livewire component renders the sequential test progress dashboard.
 */
#[Layout('components.layouts.base')]
final class TestResultsDashboard extends Component
{
    /**
     * Holds metadata about the most recent command execution.
     *
     * @var array<string, mixed>
     */
    public array $meta = [];

    /**
     * Stores all known test results keyed by their identifier.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $tests = [];

    /**
     * Remembers the execution ordering so the UI can stay consistent.
     *
     * @var array<int, string>
     */
    public array $order = [];

    /**
     * Bootstrap the component with the latest test results from disk.
     */
    public function mount(): void
    {
        $this->loadResults();
    }

    /**
     * Reload the JSON snapshot so polling keeps the UI up to date.
     */
    public function loadResults(): void
    {
        $path = $this->resultsPath();

        if (File::exists($path) !== true) {
            // Provide sensible defaults when the command has not been executed yet.
            $this->meta = [
                'is_running' => false,
                'total' => 0,
                'completed_total' => 0,
                'current_test' => null,
                'current_index' => 0,
            ];
            $this->tests = [];
            $this->order = [];

            return;
        }

        $decoded = json_decode(File::get($path) ?: '[]', true);

        if (is_array($decoded) !== true) {
            return;
        }

        $this->meta = (array) ($decoded['meta'] ?? []);
        $this->tests = (array) ($decoded['tests'] ?? []);
        $this->order = array_values(array_filter((array) ($decoded['order'] ?? [])));
    }

    /**
     * Provide an ordered list of tests for the Blade view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSortedTestsProperty(): array
    {
        $sorted = [];
        $seen = [];

        foreach ($this->order as $identifier) {
            if (isset($this->tests[$identifier])) {
                $sorted[] = $this->tests[$identifier];
                $seen[$identifier] = true;
            }
        }

        foreach ($this->tests as $identifier => $test) {
            if (isset($seen[$identifier])) {
                continue;
            }

            $sorted[] = $test;
        }

        return $sorted;
    }

    /**
     * Summarise the progress so the progress bar and counters stay accurate.
     *
     * @return array<string, float|int>
     */
    public function getSummaryProperty(): array
    {
        $tests = $this->sortedTests;
        $total = count($tests);

        $passed = 0;
        $failed = 0;
        $running = 0;

        foreach ($tests as $test) {
            $status = (string) ($test['status'] ?? 'pending');

            if ($status === 'passed') {
                $passed++;
            } elseif ($status === 'failed') {
                $failed++;
            } elseif ($status === 'running') {
                $running++;
            }
        }

        $completed = $passed + $failed;
        $pending = max($total - $completed - $running, 0);
        $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'running' => $running,
            'pending' => $pending,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }

    /**
     * Filter only the failed tests for the error summary accordion.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFailedTestsProperty(): array
    {
        return array_values(array_filter($this->sortedTests, static function (array $test): bool {
            return ($test['status'] ?? 'pending') === 'failed';
        }));
    }

    /**
     * Render the Livewire component using the dedicated Blade view.
     */
    public function render(): View
    {
        // Reload the JSON before rendering to ensure polling is reflected immediately.
        $this->loadResults();

        return view('livewire.test-results-dashboard', [
            'tests' => $this->sortedTests,
            'summary' => $this->summary,
            'failedTests' => $this->failedTests,
            'meta' => $this->meta,
        ]);
    }

    /**
     * Helper to resolve the shared JSON file path.
     */
    private function resultsPath(): string
    {
        return storage_path('app/test-results.json');
    }
}
