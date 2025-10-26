<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\TestRunner\TestResultData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use JsonException;
use Livewire\Component;

final class TestResults extends Component
{
    /**
     * @var array<string, mixed>
     */
    public array $results = [];

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
        $resultData = $this->readResults();
        $this->results = $resultData->toArray();
        $this->isRunning = $resultData->status === 'running';

        $totalTestsValue = $resultData->totalTests;
        $completedValue = $resultData->completedTests;

        $totalTests = is_numeric($totalTestsValue) ? (int) $totalTestsValue : 0;
        $completedTests = is_numeric($completedValue) ? (int) $completedValue : 0;

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

    /**
     * @return TestResultData
     */
    private function readResults(): TestResultData
    {
        $path = storage_path('app/test-results.json');

        if (! File::exists($path)) {
            return new TestResultData('no_data', 0, 0, 0, 0, [], [], null, null);
        }

        try {
            $decoded = json_decode(File::get($path) ?: '[]', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new TestResultData('invalid', 0, 0, 0, 0, [], [$exception->getMessage()], null, null);
        }

        if (! is_array($decoded)) {
            return new TestResultData('invalid', 0, 0, 0, 0, [], [], null, null);
        }

        return TestResultData::fromArray($decoded);
    }
}
