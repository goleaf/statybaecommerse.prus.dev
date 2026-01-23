<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TestResultsData
{
    public function __construct(
        public array $meta,
        public array $tests,
        public array $summary,
        public array $failedTests,
        public array $progressSegments,
        public bool $hasData,
        public string $resultsPathRelative,
        public array $statusLegend,
    ) {}
}
