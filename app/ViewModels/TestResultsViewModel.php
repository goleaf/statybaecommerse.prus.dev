<?php

declare(strict_types=1);

namespace App\ViewModels;

final class TestResultsViewModel
{
    /**
     * @param array<string, mixed>                               $meta
     * @param array<int, array<string, mixed>>                   $tests
     * @param array<string, int|float>                           $summary
     * @param array<int, array<string, mixed>>                   $failedTests
     * @param array<string, string>                              $progressSegments
     * @param array<string, array{label: string, badge: string}> $statusLegend
     */
    public function __construct(
        public readonly array $meta,
        public readonly array $tests,
        public readonly array $summary,
        public readonly array $failedTests,
        public readonly array $progressSegments,
        public readonly bool $hasData,
        public readonly string $resultsPathRelative,
        public readonly array $statusLegend,
    ) {}

    public function statusBadge(string $status): string
    {
        return $this->statusLegend[$status]['badge'] ?? $this->statusLegend['pending']['badge'];
    }

    public function statusLabel(string $status): string
    {
        return $this->statusLegend[$status]['label'] ?? $this->statusLegend['pending']['label'];
    }
}
