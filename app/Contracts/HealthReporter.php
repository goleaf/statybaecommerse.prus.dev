<?php

declare(strict_types=1);

namespace App\Contracts;

interface HealthReporter
{
    /**
     * Generate a health check report.
     *
     * @return array{status: string, checks: array<string, array<string, mixed>>, timestamp: string}
     */
    public function report(bool $includeQueue = false): array;
}
