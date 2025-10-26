<?php

declare(strict_types=1);

namespace Tests\Support\Coverage;

use function in_array;

use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\CodeCoverage as RunnerCodeCoverage;
use RuntimeException;
use SebastianBergmann\CodeCoverage\CodeCoverage as CoverageData;

use function sprintf;

final class CoverageThresholdSubscriber implements ExecutionFinishedSubscriber
{
    public function __construct(private readonly float $threshold) {}

    public function notify(ExecutionFinished $event): void
    {
        if ($this->shouldSkip()) {
            return;
        }

        $coverage = RunnerCodeCoverage::instance();

        if (! $coverage->isActive()) {
            throw new RuntimeException('Code coverage collection is disabled. Enable Xdebug or PCOV to enforce coverage thresholds.');
        }

        $data = $coverage->codeCoverage();
        $percentage = $this->lineCoveragePercentage($data);

        if ($percentage < $this->threshold) {
            throw new RuntimeException(sprintf(
                'Code coverage %.2f%% is below the configured threshold of %.2f%%.',
                $percentage,
                $this->threshold,
            ));
        }
    }

    private function lineCoveragePercentage(CoverageData $coverage): float
    {
        return $coverage
            ->getReport()
            ->percentageOfExecutedLines()
            ->asFloat();
    }

    private function shouldSkip(): bool
    {
        $flag = $_SERVER['SKIP_COVERAGE_THRESHOLD'] ?? getenv('SKIP_COVERAGE_THRESHOLD');

        if ($flag === false || $flag === null) {
            return false;
        }

        if (is_string($flag)) {
            return in_array(strtolower($flag), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $flag;
    }
}
