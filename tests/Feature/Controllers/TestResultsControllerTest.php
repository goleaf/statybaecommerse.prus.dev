<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Services\TestResultsService;
use App\ViewModels\TestResultsViewModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class TestResultsControllerTest extends TestCase
{
    public function test_it_renders_the_test_results_view_using_the_fallback_locale(): void
    {
        // Configure mismatched application and fallback locales so the controller's override is observable.
        config()->set('app.locale', 'lt');
        config()->set('app.fallback_locale', 'en');

        $temporaryPath = storage_path('app/test-results-controller-fixture.json');

        // Seed a deterministic dataset so the underlying service produces a populated view model.
        File::put($temporaryPath, json_encode([
            'meta'  => [
                'created_at'   => '2024-01-01T00:00:00Z',
                'is_running'   => false,
                'total'        => 1,
                'completed_at' => '2024-01-01T00:01:00Z',
            ],
            'tests' => [
                'feature:test' => [
                    'id'                => 'feature:test',
                    'status'            => 'passed',
                    'groups'            => ['feature'],
                    'hash'              => 'abc123',
                    'output'            => 'All good',
                    'error_output'      => null,
                    'last_run_duration' => 1.23,
                    'run_at'            => '2024-01-01T00:00:30Z',
                ],
            ],
            'order' => ['feature:test'],
        ], JSON_PRETTY_PRINT));

        // Inject a real service instance pointed at the temporary dataset to avoid mocking the final class.
        $service = new TestResultsService($temporaryPath);
        $this->app->instance(TestResultsService::class, $service);

        try {
            // Hit the test results route so the controller can render the dashboard preview response.
            $response = $this->get(route('test-results'));

            // The controller should respond successfully when the view renders without issues.
            $response->assertOk();

            // Ensure the Blade template powering the dashboard remains the expected page.
            $response->assertViewIs('pages.test-results');

            // Confirm the resolved view model is forwarded to the view layer with a populated dataset.
            $response->assertViewHas('viewModel', static function ($viewModel): bool {
                if (! $viewModel instanceof TestResultsViewModel) {
                    return false;
                }

                return $viewModel->hasData === true && $viewModel->summary['total'] === 1;
            });

            // Verify the locale override forced the request to adopt the configured fallback language.
            self::assertSame('en', App::getLocale());
        } finally {
            // Ensure the temporary fixture is removed so future tests remain isolated.
            File::delete($temporaryPath);
        }
    }
}
