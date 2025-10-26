<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\Storefront\Testing\TestResultsData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use JsonException;
use Livewire\Component;

final class TestResults extends Component
{
    public TestResultsData $resultsData;

    public bool $isRunning = false;

    public int $progress = 0;

    /**
     * @var array<int, string>
     */
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
        $this->resultsData = $this->readResults();
        $this->isRunning = $this->resultsData->status === 'running';

        $totalTests = max(0, $this->resultsData->totalTests);
        $completedTests = max(0, min($this->resultsData->completedTests, $totalTests));

        $this->progress = $totalTests > 0
            ? (int) round(($completedTests / $totalTests) * 100)
            : 0;
    }

    public function render(): View
    {
        return view('livewire.test-results');
    }

    /**
     * Expose an array representation for Blade compatibility while retaining typed data internally.
     *
     * @return array<string, mixed>
     */
    public function getResultsProperty(): array
    {
        return $this->resultsData->toArray();
    }

    private function readResults(): TestResultsData
    {
        $path = storage_path('app/test-results.json');

        if (! File::exists($path)) {
            return TestResultsData::fromArray([]);
        }

        try {
            $decoded = json_decode(File::get($path) ?: '[]', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return TestResultsData::fromArray([]);
        }

        if (! is_array($decoded)) {
            return TestResultsData::fromArray([]);
        }

        return TestResultsData::fromArray($decoded);
    }
}
