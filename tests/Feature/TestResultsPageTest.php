<?php

declare(strict_types=1);

use App\Services\TestResultsService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $path = storage_path('app/test-results.json');

    if (File::exists($path)) {
        File::delete($path);
    }
});

afterEach(function (): void {
    $path = storage_path('app/test-results.json');

    if (File::exists($path)) {
        File::delete($path);
    }
});

it('feature: shows empty state when no dataset exists', function (): void {
    File::delete(storage_path('app/test-results.json'));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee(__('frontend.test_results.empty.title'));
    $response->assertSee(__('frontend.test_results.empty.description'));
    $response->assertSee('<code>php artisan project:test</code>', false);
});

it('feature: renders ordered dataset with translated labels', function (): void {
    $mockData = [
        'meta' => [
            'is_running'      => true,
            'total'           => 3,
            'completed_total' => 1,
            // Reference the new dashboard placeholder test to keep the JSON
            // fixture aligned with the renamed class.
            'current_test'    => 'Tests\Feature\DashboardFixtureTest::it_passes',
            'current_index'   => 2,
            'created_at'      => now()->toIso8601String(),
            'started_at'      => now()->subMinutes(5)->toIso8601String(),
            'last_updated_at' => now()->toIso8601String(),
        ],
        'tests' => [
            'Tests\Feature\DashboardFixtureTest::it_passes' => [
                'id'                => 'Tests\Feature\DashboardFixtureTest::it_passes',
                'status'            => 'running',
                'hash'              => md5('Tests\Feature\DashboardFixtureTest::it_passes'),
                'groups'            => ['feature', 'smoke'],
                'output'            => 'Running feature test',
                'error_output'      => '',
                'last_run_duration' => 1.234,
            ],
            'Tests\Feature\OtherTest::it_passes' => [
                'id'                => 'Tests\Feature\OtherTest::it_passes',
                'status'            => 'passed',
                'hash'              => md5('Tests\Feature\OtherTest::it_passes'),
                'groups'            => ['feature'],
                'output'            => 'All assertions passed',
                'error_output'      => '',
                'last_run_duration' => 0.456,
            ],
            'Tests\Feature\FailingTest::it_fails' => [
                'id'                => 'Tests\Feature\FailingTest::it_fails',
                'status'            => 'failed',
                'hash'              => md5('Tests\Feature\FailingTest::it_fails'),
                'groups'            => ['feature', 'critical'],
                'output'            => 'Expectation failed',
                'error_output'      => 'Failed asserting that false is true.',
                'last_run_duration' => 0.789,
            ],
        ],
        'order' => [
            'Tests\Feature\DashboardFixtureTest::it_passes',
            'Tests\Feature\OtherTest::it_passes',
            'Tests\Feature\FailingTest::it_fails',
        ],
    ];

    File::ensureDirectoryExists(storage_path('app'));
    File::put(storage_path('app/test-results.json'), json_encode($mockData, JSON_PRETTY_PRINT));

    /** @var TestResultsService $service */
    $service = App::make(TestResultsService::class);
    $viewModel = $service->buildViewModel();

    expect($viewModel->summary['total'])
        ->toBe(3)
        ->and($viewModel->summary['passed'])
        ->toBe(1)
        ->and($viewModel->summary['running'])
        ->toBe(1)
        ->and($viewModel->summary['failed'])
        ->toBe(1);

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee(__('frontend.test_results.status.running'));
    $response->assertSee('Tests\Feature\DashboardFixtureTest::it_passes');
    $response->assertSee('Tests\Feature\FailingTest::it_fails');
    $response->assertSee('Expectation failed');
    $response->assertSee('Failed asserting that false is true.');
});

it('feature: supports english locale translations', function (): void {
    app()->setLocale('en');

    File::ensureDirectoryExists(storage_path('app'));
    File::put(storage_path('app/test-results.json'), json_encode([
        'meta' => [
            'is_running'      => false,
            'total'           => 1,
            'completed_total' => 1,
            'created_at'      => now()->toIso8601String(),
            'started_at'      => now()->subMinutes(2)->toIso8601String(),
            'completed_at'    => now()->toIso8601String(),
        ],
        'tests' => [
            'Tests\Unit\DemoTest::test_demo' => [
                'id'                => 'Tests\Unit\DemoTest::test_demo',
                'status'            => 'passed',
                'hash'              => md5('Tests\Unit\DemoTest::test_demo'),
                'groups'            => [],
                'output'            => 'pass',
                'error_output'      => '',
                'last_run_duration' => 0.111,
            ],
        ],
        'order' => ['Tests\Unit\DemoTest::test_demo'],
    ], JSON_PRETTY_PRINT));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee(__('frontend.test_results.status.passed', [], 'en'));
    $response->assertSee(__('frontend.test_results.summary.success_rate', [], 'en'));
});
