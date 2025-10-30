<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Components;

use App\Livewire\TestResults;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

final class TestResultsComponentTest extends TestCase
{
    private string $resultsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the storage path so every test interacts with the same snapshot location.
        $this->resultsPath = storage_path('app/test-results.json');

        // Guarantee the directory exists and clear any previous fixture to avoid state leakage.
        File::ensureDirectoryExists(dirname($this->resultsPath));
        File::delete($this->resultsPath);
    }

    protected function tearDown(): void
    {
        // Remove the generated snapshot when the test has finished to keep the filesystem clean.
        if (File::exists($this->resultsPath)) {
            File::delete($this->resultsPath);
        }

        parent::tearDown();
    }

    public function test_component_renders_failures_from_snapshot_payload(): void
    {
        $payload = [
            'status'          => 'completed',
            'total_tests'     => 3,
            'completed_tests' => 3,
            'passed_tests'    => 2,
            'failed_tests'    => 1,
            'tests'           => [
                [
                    'file'    => 'tests/Feature/PassingTest.php',
                    'status'  => 'passed',
                    'output'  => 'ok',
                    'run_at'  => '2025-10-01T10:00:00Z',
                    'error'   => '',
                ],
            ],
            'errors'          => [
                [
                    'file'   => 'tests/Feature/BrokenTest.php',
                    'error'  => 'Failed asserting that false is true.',
                    'output' => "Failed asserting that false is true.\n--- Expected\n+++ Actual\n@@\n-true\n+false\n",
                ],
            ],
            'started_at'      => '2025-10-01T09:55:00Z',
            'completed_at'    => '2025-10-01T10:05:00Z',
        ];

        // Persist a realistic snapshot so the Livewire component hydrates from actual disk data.
        File::put(
            $this->resultsPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $component = Livewire::test(TestResults::class);

        // The component should mark the run as complete and surface failure details for operators.
        $component->assertSet('isRunning', false);
        $component->assertSet('progress', 100);
        $component->assertSee('BrokenTest.php');
        $component->assertSee('Failed asserting that false is true.');
        $component->assertSee('2025-10-01T10:05:00Z');
    }

    public function test_refresh_results_recovers_when_snapshot_is_removed(): void
    {
        $payload = [
            'status'          => 'running',
            'total_tests'     => 5,
            'completed_tests' => 2,
            'passed_tests'    => 2,
            'failed_tests'    => 0,
            'tests'           => [],
            'errors'          => [],
            'started_at'      => '2025-10-02T09:00:00Z',
            'completed_at'    => null,
        ];

        // Seed the snapshot to simulate an in-progress run.
        File::put(
            $this->resultsPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $component = Livewire::test(TestResults::class);

        // Ensure the initial state reflects the running payload before removing the file.
        $component->assertSet('isRunning', true);
        $component->assertSet('progress', 40);

        // Delete the snapshot and request a refresh to mimic a job finishing and clearing the artifact.
        File::delete($this->resultsPath);
        $component->call('refreshResults');

        // After refreshing, the component should revert to the no data fallback state.
        $this->assertSame('no_data', $component->get('results')['status']);
        $component->assertSet('isRunning', false);
        $component->assertSet('progress', 0);
    }
}
