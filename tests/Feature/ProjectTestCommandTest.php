<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing test results file
    $resultsPath = storage_path('app/test-results.json');
    if (File::exists($resultsPath)) {
        File::delete($resultsPath);
    }
});

afterEach(function () {
    // Clean up test results file after each test
    $resultsPath = storage_path('app/test-results.json');
    if (File::exists($resultsPath)) {
        File::delete($resultsPath);
    }
});

it('feature: can discover tests using phpunit --list-tests', function () {
    $this
        ->artisan('project:test')
        ->expectsOutput('Sequential test execution finished.')
        ->assertSuccessful();

    // Verify test results file was created
    $resultsPath = storage_path('app/test-results.json');
    expect(File::exists($resultsPath))->toBeTrue();
});

it('feature: creates test results json file', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    expect(File::exists($resultsPath))->toBeTrue();

    $contents = File::get($resultsPath);
    $data = json_decode($contents, true);

    expect($data)->toBeArray();
    expect($data)->toHaveKeys(['meta', 'tests', 'order']);
});

it('feature: test results contain meta information', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['meta'])->toBeArray();
    expect($data['meta'])->toHaveKeys([
        'created_at',
        'started_at',
        'completed_at',
        'is_running',
        'current_test',
        'current_index',
        'completed_total',
        'total',
        'last_updated_at',
    ]);
});

it('feature: test results contain tests array', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['tests'])->toBeArray();
    expect(count($data['tests']))->toBeGreaterThan(0);
});

it('feature: each test has required fields', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    $firstTest = reset($data['tests']);
    expect($firstTest)->toBeArray();
    expect($firstTest)->toHaveKeys([
        'id',
        'hash',
        'groups',
        'status',
    ]);
});

it('feature: meta is_running is false after completion', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['meta']['is_running'])->toBeFalse();
});

it('feature: completed_at is set after completion', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['meta']['completed_at'])->not->toBeNull();
});

it('feature: total tests count matches discovered tests', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['meta']['total'])->toBe(count($data['tests']));
});

it('feature: order array contains test identifiers', function () {
    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $resultsPath = storage_path('app/test-results.json');
    $data = json_decode(File::get($resultsPath), true);

    expect($data['order'])->toBeArray();
    expect(count($data['order']))->toBeGreaterThan(0);
    expect($data['order'][0])->toBeString();
});

it('feature: can handle empty test results', function () {
    // Create empty results file
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(storage_path('app'));
    File::put($resultsPath, json_encode(['meta' => [], 'tests' => [], 'order' => []]));

    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $data = json_decode(File::get($resultsPath), true);
    expect($data['meta']['total'])->toBeGreaterThan(0);
});

it('feature: preserves previous test data', function () {
    // Create initial results file
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(storage_path('app'));
    $initialData = [
        'meta' => [
            'created_at'   => '2024-01-01T00:00:00+00:00',
            'custom_field' => 'test_value',
        ],
        'tests' => [],
        'order' => [],
    ];
    File::put($resultsPath, json_encode($initialData));

    $this
        ->artisan('project:test')
        ->assertSuccessful();

    $data = json_decode(File::get($resultsPath), true);
    expect($data['meta']['created_at'])->toBe('2024-01-01T00:00:00+00:00');
});

it('feature: test status page route exists', function () {
    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertViewIs('pages.test-results');
});

it('feature: test status page displays correctly without results', function () {
    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee(__('frontend.test_results.empty.title'));
    $response->assertSee(__('frontend.test_results.empty.description'));
});

it('feature: test status page displays results when available', function () {
    // Create mock test results
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(storage_path('app'));
    $mockData = [
        'meta' => [
            'is_running'      => false,
            'total'           => 2,
            'completed_total' => 2,
            'current_test'    => null,
            'current_index'   => 2,
        ],
        'tests' => [
            'Tests\Unit\ExampleTest::testExample' => [
                'id'                => 'Tests\Unit\ExampleTest::testExample',
                'hash'              => md5('Tests\Unit\ExampleTest::testExample'),
                'groups'            => [],
                'status'            => 'passed',
                'output'            => 'Test passed successfully',
                'error_output'      => '',
                'last_run_duration' => 0.123,
            ],
            'Tests\Feature\ExampleTest::testExample' => [
                'id'                => 'Tests\Feature\ExampleTest::testExample',
                'hash'              => md5('Tests\Feature\ExampleTest::testExample'),
                'groups'            => [],
                'status'            => 'failed',
                'output'            => '',
                'error_output'      => 'Test failed',
                'last_run_duration' => 0.456,
            ],
        ],
        'order' => [
            'Tests\Unit\ExampleTest::testExample',
            'Tests\Feature\ExampleTest::testExample',
        ],
    ];
    File::put($resultsPath, json_encode($mockData));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee('Tests\Unit\ExampleTest::testExample');
    $response->assertSee('Tests\Feature\ExampleTest::testExample');
    $response->assertSee(__('frontend.test_results.status.passed'));
    $response->assertSee(__('frontend.test_results.status.failed'));
});

it('feature: test status page shows running status', function () {
    // Create mock test results with running status
    $resultsPath = storage_path('app/test-results.json');
    File::ensureDirectoryExists(storage_path('app'));
    $mockData = [
        'meta' => [
            'is_running'      => true,
            'total'           => 3,
            'completed_total' => 1,
            'current_test'    => 'Tests\Unit\CurrentTest::testRunning',
            'current_index'   => 2,
        ],
        'tests' => [
            'Tests\Unit\CompletedTest::testDone' => [
                'id'     => 'Tests\Unit\CompletedTest::testDone',
                'hash'   => md5('Tests\Unit\CompletedTest::testDone'),
                'groups' => [],
                'status' => 'passed',
            ],
            'Tests\Unit\CurrentTest::testRunning' => [
                'id'     => 'Tests\Unit\CurrentTest::testRunning',
                'hash'   => md5('Tests\Unit\CurrentTest::testRunning'),
                'groups' => [],
                'status' => 'running',
            ],
            'Tests\Unit\PendingTest::testWaiting' => [
                'id'     => 'Tests\Unit\PendingTest::testWaiting',
                'hash'   => md5('Tests\Unit\PendingTest::testWaiting'),
                'groups' => [],
                'status' => 'pending',
            ],
        ],
        'order' => [
            'Tests\Unit\CompletedTest::testDone',
            'Tests\Unit\CurrentTest::testRunning',
            'Tests\Unit\PendingTest::testWaiting',
        ],
    ];
    File::put($resultsPath, json_encode($mockData));

    $response = $this->get(route('test-results'));

    $response->assertSuccessful();
    $response->assertSee(__('frontend.test_results.status.running'));
    $response->assertSee('Tests\Unit\CurrentTest::testRunning');
});

it('feature: minimal layout does not include header and footer', function () {
    $layoutContent = File::get(resource_path('views/layouts/minimal.blade.php'));

    expect($layoutContent)->not->toContain('<x-layouts.header');
    expect($layoutContent)->not->toContain('<x-layouts.footer');
    expect($layoutContent)->toContain('<!DOCTYPE html>');
    expect($layoutContent)->toContain('</html>');
});
