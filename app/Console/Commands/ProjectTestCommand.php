<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Class ProjectTestCommand orchestrates running the application's tests sequentially.
 *
 * The command keeps a shared JSON file updated so the Livewire dashboard can
 * display progress and historical outcomes without relying on a database.
 */
final class ProjectTestCommand extends Command
{
    /**
     * File name used to persist the structured test run information.
     */
    private const RESULTS_FILENAME = 'test-results.json';

    /**
     * The command signature exposed to the artisan CLI.
     */
    protected $signature = 'project:test';

    /**
     * A short description that appears in the artisan command list.
     */
    protected $description = 'Run PHPUnit tests sequentially and persist structured progress data.';

    /**
     * Handle the execution of the project:test command.
     */
    public function handle(): int
    {
        // Collect the tests that need to be executed one by one.
        $tests = $this->collectTests();

        if ($tests === []) {
            // When we cannot discover any tests, log feedback for the operator.
            $this->error('No tests were discovered by phpunit --list-tests-json.');

            return self::FAILURE;
        }

        // Prepare the persisted state so the UI can react immediately.
        $state = $this->initialiseState($tests);
        $this->persistState($state);

        $total = count($tests);

        foreach ($tests as $index => $test) {
            $identifier = (string) ($test['id'] ?? '');

            if ($identifier === '') {
                continue;
            }

            // Update the shared state to reflect the running test.
            $state['meta']['is_running'] = true;
            $state['meta']['current_test'] = $identifier;
            $state['meta']['current_index'] = $index + 1;
            $state['meta']['last_updated_at'] = now()->toIso8601String();

            $state['tests'][$identifier]['status'] = 'running';
            $state['tests'][$identifier]['started_at'] = now()->toIso8601String();
            $state['tests'][$identifier]['run_count'] = (int) ($state['tests'][$identifier]['run_count'] ?? 0) + 1;

            $this->persistState($state);

            $this->line(sprintf('Running %s (%d of %d)', $identifier, $index + 1, $total));

            // Execute the test in an isolated PHP process to avoid memory exhaustion.
            $result = $this->runSingleTest($identifier);

            $state['tests'][$identifier]['finished_at'] = now()->toIso8601String();
            $state['tests'][$identifier]['status'] = $result['status'];
            $state['tests'][$identifier]['output'] = $result['output'];
            $state['tests'][$identifier]['error_output'] = $result['error_output'];
            $state['tests'][$identifier]['last_run_exit_code'] = $result['exit_code'];
            $state['tests'][$identifier]['last_run_duration'] = $result['duration'];

            $state['meta']['last_updated_at'] = now()->toIso8601String();
            $state['meta']['completed_total'] = $this->countCompletedTests($state['tests']);

            $this->persistState($state);
        }

        // Mark the run as finished so the UI can stop showing the running indicator.
        $state['meta']['is_running'] = false;
        $state['meta']['current_test'] = null;
        $state['meta']['current_index'] = $total;
        $state['meta']['completed_at'] = now()->toIso8601String();
        $state['meta']['last_updated_at'] = now()->toIso8601String();
        $state['meta']['completed_total'] = $this->countCompletedTests($state['tests']);

        $this->persistState($state);

        $this->info('Sequential test execution finished.');

        return self::SUCCESS;
    }

    /**
     * Build the initial state structure so we do not remove previously stored data.
     *
     * @param  array<int, array<string, mixed>> $tests
     * @return array<string, mixed>
     */
    private function initialiseState(array $tests): array
    {
        $state = $this->loadExistingState();

        $state['meta'] = array_merge(
            [
                'created_at'      => $state['meta']['created_at'] ?? now()->toIso8601String(),
                'started_at'      => now()->toIso8601String(),
                'completed_at'    => $state['meta']['completed_at'] ?? null,
                'is_running'      => true,
                'current_test'    => null,
                'current_index'   => 0,
                'completed_total' => $state['meta']['completed_total'] ?? 0,
                'total'           => count($tests),
                'last_updated_at' => now()->toIso8601String(),
            ],
            (array) ($state['meta'] ?? [])
        );

        $state['order'] = array_values(array_unique(array_merge($state['order'] ?? [], array_map(
            static fn (array $test): string => (string) ($test['id'] ?? ''),
            $tests
        ))));

        foreach ($tests as $test) {
            $identifier = (string) ($test['id'] ?? '');

            if ($identifier === '') {
                continue;
            }

            $existing = (array) ($state['tests'][$identifier] ?? []);

            $state['tests'][$identifier] = array_merge(
                $existing,
                [
                    'id'     => $identifier,
                    'hash'   => md5($identifier),
                    'groups' => array_values(array_filter((array) ($test['groups'] ?? []))),
                    'status' => $existing['status'] ?? 'pending',
                ]
            );
        }

        return $state;
    }

    /**
     * Load the existing JSON file if it is present on disk.
     *
     * @return array<string, mixed>
     */
    private function loadExistingState(): array
    {
        $path = $this->resultsPath();

        if (File::exists($path) !== true) {
            return [
                'meta'  => [],
                'tests' => [],
                'order' => [],
            ];
        }

        $contents = File::get($path);
        $decoded = json_decode($contents ?: '[]', true);

        if (is_array($decoded) !== true) {
            return [
                'meta'  => [],
                'tests' => [],
                'order' => [],
            ];
        }

        $decoded['meta'] = (array) ($decoded['meta'] ?? []);
        $decoded['tests'] = (array) ($decoded['tests'] ?? []);
        $decoded['order'] = array_values(array_filter((array) ($decoded['order'] ?? [])));

        return $decoded;
    }

    /**
     * Persist the current state to the JSON file so that the UI can read it.
     *
     * @param array<string, mixed> $state
     */
    private function persistState(array $state): void
    {
        File::ensureDirectoryExists(storage_path('app'));

        File::put(
            $this->resultsPath(),
            json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}',
            true
        );
    }

    /**
     * Execute a single PHPUnit test and capture the outcome details.
     *
     * @return array{status: string, output: string, error_output: string, exit_code: int, duration: float}
     */
    private function runSingleTest(string $identifier): array
    {
        $start = microtime(true);

        $regex = '/' . preg_quote($identifier, '/') . '(?:\swith\sdata\sset\s.+)?$/';

        $process = new Process([
            './vendor/bin/phpunit',
            '--filter',
            $regex,
            '--testdox',
            '--colors=never',
        ], base_path());

        // Disable the timeout so long running tests are still respected.
        $process->setTimeout(null);
        $process->run();

        $duration = microtime(true) - $start;

        return [
            'status'       => $process->isSuccessful() ? 'passed' : 'failed',
            'output'       => trim($process->getOutput()),
            'error_output' => trim($process->getErrorOutput()),
            'exit_code'    => $process->getExitCode() ?? 1,
            'duration'     => $duration,
        ];
    }

    /**
     * Discover the test cases using PHPUnit's JSON listing.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectTests(): array
    {
        $process = new Process([
            './vendor/bin/phpunit',
            '--list-tests-json',
        ], base_path());

        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful() !== true) {
            $this->error($process->getErrorOutput() !== '' ? $process->getErrorOutput() : 'Unable to list tests.');

            return [];
        }

        $decoded = json_decode($process->getOutput() ?: '[]', true);

        if (is_array($decoded) !== true) {
            return [];
        }

        $tests = (array) ($decoded['tests'] ?? []);

        return array_values(array_filter(array_map(
            static function ($test): ?array {
                if (! is_array($test)) {
                    return null;
                }

                $identifier = (string) ($test['id'] ?? '');

                if ($identifier === '') {
                    return null;
                }

                return [
                    'id'     => $identifier,
                    'groups' => array_values(array_filter((array) ($test['groups'] ?? []))),
                ];
            },
            $tests
        )));
    }

    /**
     * Count how many tests are no longer pending to inform the UI.
     *
     * @param array<string, array<string, mixed>> $tests
     */
    private function countCompletedTests(array $tests): int
    {
        return collect($tests)
            ->filter(static function (array $test): bool {
                $status = (string) ($test['status'] ?? 'pending');

                return in_array($status, ['passed', 'failed'], true);
            })
            ->count();
    }

    /**
     * Resolve the absolute path for the JSON file.
     */
    private function resultsPath(): string
    {
        return storage_path('app/' . self::RESULTS_FILENAME);
    }
}
