<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $resultsPath = storage_path('app/test-results.json');

    if (File::exists($resultsPath)) {
        File::delete($resultsPath);
    }
});

afterEach(function (): void {
    $resultsPath = storage_path('app/test-results.json');

    if (File::exists($resultsPath)) {
        File::delete($resultsPath);
    }
});

it('shows empty state when no results exist', function (): void {
    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee('Project Test Results');
    $response->assertSee('Trigger php artisan project:test to populate the dashboard.');
});

it('renders completed summary when results are available', function (): void {
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(dirname($resultsPath));

    File::put($resultsPath, json_encode([
        'meta' => [
            'is_running'      => false,
            'current_test'    => null,
            'current_index'   => 0,
            'started_at'      => '2025-10-24T09:00:00+00:00',
            'last_updated_at' => '2025-10-24T09:05:00+00:00',
            'completed_at'    => '2025-10-24T09:06:00+00:00',
        ],
        'tests' => [
            'Tests\\Feature\\DashboardFixtureTest::it_passes' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::it_passes',
                'status'            => 'passed',
                'groups'            => ['feature'],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::it_passes'),
                'last_run_duration' => 1.42,
                'output'            => 'ok',
                'error_output'      => '',
            ],
            'Tests\\Feature\\DashboardFixtureTest::it_fails' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::it_fails',
                'status'            => 'failed',
                'groups'            => ['feature'],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::it_fails'),
                'last_run_duration' => 2.54,
                'output'            => 'failed',
                'error_output'      => 'Expectation failed',
            ],
        ],
        'order' => [
            'Tests\\Feature\\DashboardFixtureTest::it_passes',
            'Tests\\Feature\\DashboardFixtureTest::it_fails',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee('Overall progress');
    $response->assertSee('Passed: 1');
    $response->assertSee('Failed: 1');
    $response->assertSee('Tests\\Feature\\DashboardFixtureTest::it_passes');
    $response->assertSee('Tests\\Feature\\DashboardFixtureTest::it_fails');
    $response->assertSee('Expectation failed');
});

it('shows running indicator when the suite is in progress', function (): void {
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(dirname($resultsPath));

    File::put($resultsPath, json_encode([
        'meta' => [
            'is_running'      => true,
            'current_test'    => 'Tests\\Feature\\DashboardFixtureTest::in_progress',
            'current_index'   => 2,
            'completed_total' => 1,
            'total'           => 3,
        ],
        'tests' => [
            'Tests\\Feature\\DashboardFixtureTest::completed' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::completed',
                'status'            => 'passed',
                'groups'            => [],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::completed'),
                'last_run_duration' => 1.0,
                'output'            => 'done',
                'error_output'      => '',
            ],
            'Tests\\Feature\\DashboardFixtureTest::in_progress' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::in_progress',
                'status'            => 'running',
                'groups'            => ['wip'],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::in_progress'),
                'last_run_duration' => null,
                'output'            => 'running',
                'error_output'      => '',
            ],
            'Tests\\Feature\\DashboardFixtureTest::pending' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::pending',
                'status'            => 'pending',
                'groups'            => [],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::pending'),
                'last_run_duration' => null,
                'output'            => '',
                'error_output'      => '',
            ],
        ],
        'order' => [
            'Tests\\Feature\\DashboardFixtureTest::completed',
            'Tests\\Feature\\DashboardFixtureTest::in_progress',
            'Tests\\Feature\\DashboardFixtureTest::pending',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee('Running Tests');
    $response->assertSee('This dashboard refreshes automatically every five seconds.');
    $response->assertSee('Tests\\Feature\\DashboardFixtureTest::in_progress');
});

it('lists failed test details in the failure summary', function (): void {
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(dirname($resultsPath));

    File::put($resultsPath, json_encode([
        'meta' => [
            'is_running'      => false,
            'current_test'    => null,
            'current_index'   => 3,
            'total'           => 3,
            'completed_total' => 3,
        ],
        'tests' => [
            'Tests\\Feature\\DashboardFixtureTest::failed_case' => [
                'id'                => 'Tests\\Feature\\DashboardFixtureTest::failed_case',
                'status'            => 'failed',
                'groups'            => ['feature'],
                'hash'              => md5('Tests\\Feature\\DashboardFixtureTest::failed_case'),
                'last_run_duration' => 3.75,
                'output'            => 'output log',
                'error_output'      => 'detailed failure output',
            ],
        ],
        'order' => [
            'Tests\\Feature\\DashboardFixtureTest::failed_case',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee('Failed test details');
    $response->assertSee('Tests\\Feature\\DashboardFixtureTest::failed_case');
    $response->assertSee('detailed failure output');
    $response->assertSee('output log');
});
