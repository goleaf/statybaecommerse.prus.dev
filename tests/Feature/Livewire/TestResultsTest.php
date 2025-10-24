<?php

declare(strict_types=1);

use App\Livewire\TestResults;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;

it('renders fallback data when no test results snapshot exists', function (): void {
    File::shouldReceive('exists')->once()->with(storage_path('app/test-results.json'))->andReturnFalse();

    Livewire::test(TestResults::class)
        ->assertSet('results.status', 'no_data')
        ->assertSee(Lang::get('frontend.test_results.no_data.title'))
        ->assertSee(Lang::get('frontend.test_results.no_data.command'))
        ->assertStatus(200);
});

it('calculates progress and status from partial snapshot data', function (): void {
    File::shouldReceive('exists')->once()->with(storage_path('app/test-results.json'))->andReturnTrue();
    File::shouldReceive('get')->once()->andReturn(json_encode([
        'status'          => 'running',
        'total_tests'     => 10,
        'completed_tests' => 4,
        'passed_tests'    => 3,
        'failed_tests'    => 1,
        'tests'           => [
            ['file' => 'tests/Feature/ExampleTest.php', 'status' => 'passed', 'run_at' => '2025-10-24 12:00:00', 'output' => 'ok'],
        ],
        'errors' => [],
    ]));

    Livewire::test(TestResults::class)
        ->assertSet('isRunning', true)
        ->assertSet('progress', 40)
        ->assertSee(Lang::get('frontend.test_results.progress.completed', ['completed' => 4, 'total' => 10]))
        ->assertSee('ExampleTest.php')
        ->assertStatus(200);
});

it('clips completed tests to the total and handles invalid json gracefully', function (): void {
    File::shouldReceive('exists')->once()->with(storage_path('app/test-results.json'))->andReturnTrue();
    File::shouldReceive('get')->once()->andReturn('}{invalid json');

    Livewire::test(TestResults::class)
        ->assertSet('progress', 0)
        ->assertSee(Lang::get('frontend.test_results.no_data.title'));
});
