<?php

declare(strict_types=1);

namespace Tests\Diagnostics\Coverage;

use function in_array;
use function sprintf;
use function strtolower;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\CodeCoverage as RunnerCoverage;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RuntimeException;
use SebastianBergmann\CodeCoverage\Util\Percentage;
use function extension_loaded;
use function ini_get;
use function str_contains;

/**
 * PHPUnit extension that enforces a configurable minimum line coverage percentage.
 */
final class MinimumCoverageExtension implements Extension
{
    /**
     * @var float Default coverage threshold applied when no environment override is provided.
     */
    private float $defaultThreshold;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        // Parse the default threshold passed from phpunit.xml and fall back to a conservative 65% when missing.
        $this->defaultThreshold = $parameters->has('threshold')
            ? (float) $parameters->get('threshold')
            : 65.0;

        if (! self::coverageDriverAvailable()) {
            return;
        }

        // Register the subscriber that checks the aggregated coverage once execution finishes.
        $facade->registerSubscriber(new class($this->defaultThreshold) implements ExecutionFinishedSubscriber {
            public function __construct(private readonly float $defaultThreshold)
            {
            }

            public function notify(ExecutionFinished $event): void
            {
                // Allow opting out of the guard during local development or in constrained CI jobs.
                if (self::isSkipRequested()) {
                    return;
                }

                $coverage = RunnerCoverage::instance();

                if (! $coverage->isActive()) {
                    return;
                }

                $percentage = $coverage->codeCoverage()
                    ->getReport()
                    ->percentageOfExecutedLines();

                $threshold = self::resolveThreshold($this->defaultThreshold);

                if (self::belowThreshold($percentage, $threshold)) {
                    throw new RuntimeException(
                        sprintf(
                            'Global line coverage %.2f%% is below the enforced minimum of %.2f%%.',
                            $percentage->asFloat(),
                            $threshold,
                        ),
                    );
                }
            }

            private static function isSkipRequested(): bool
            {
                return in_array(strtolower((string) getenv('SKIP_COVERAGE_THRESHOLD')), ['1', 'true', 'yes'], true);
            }

            private static function resolveThreshold(float $fallback): float
            {
                $override = getenv('COVERAGE_THRESHOLD');

                if ($override === false || $override === '') {
                    return $fallback;
                }

                return (float) $override;
            }

            private static function belowThreshold(Percentage $percentage, float $threshold): bool
            {
                return $percentage->asFloat() + 1e-6 < $threshold;
            }
        });

        // Ensure coverage is collected so the subscriber can evaluate the final metrics.
        $facade->requireCodeCoverageCollection();
    }

    private static function coverageDriverAvailable(): bool
    {
        if (extension_loaded('xdebug')) {
            $mode = (string) ini_get('xdebug.mode');

            return $mode === '' || str_contains($mode, 'coverage');
        }

        if (extension_loaded('pcov')) {
            return true;
        }

        return false;
    }
}
