<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Livewire\Component;

final class TestResults extends Component
{
    public array $results = [];

    public bool $isRunning = false;

    public int $progress = 0;

    protected $listeners = ['refreshResults'];

    public function mount(): void
    {
        $this->loadResults();
    }

    public function refreshResults(): void
    {
        $this->loadResults();
    }

    public function loadResults(): void
    {
        $this->results = $this->readResults();
        $this->isRunning = ($this->results['status'] ?? 'completed') === 'running';

        $totalTests = (int) ($this->results['total_tests'] ?? 0);
        $completedTests = (int) ($this->results['completed_tests'] ?? 0);

        if ($totalTests > 0) {
            $completedTests = max(0, min($completedTests, $totalTests));
            $this->progress = (int) round(($completedTests / $totalTests) * 100);
        } else {
            $this->progress = 0;
        }
    }

    public function render(): View
    {
        return view('livewire.test-results');
    }

    private function readResults(): array
    {
        $defaults = [
            'status'          => 'no_data',
            'total_tests'     => 0,
            'completed_tests' => 0,
            'passed_tests'    => 0,
            'failed_tests'    => 0,
            'tests'           => [],
            'errors'          => [],
            'started_at'      => null,
            'completed_at'    => null,
        ];

        $path = storage_path('app/test-results.json');

        if (! File::exists($path)) {
            return $defaults;
        }

        $decoded = json_decode(File::get($path) ?: '[]', true);

        if (! is_array($decoded)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $decoded);
    }
}
