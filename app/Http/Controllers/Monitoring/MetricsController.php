<?php

declare(strict_types=1);

namespace App\Http\Controllers\Monitoring;

use App\Support\Monitoring\ApplicationMetrics;
use Illuminate\Http\Response;

final class MetricsController
{
    public function __construct(private readonly ApplicationMetrics $metrics)
    {
    }

    public function __invoke(): Response
    {
        return response(
            $this->metrics->toPrometheus(),
            200,
            ['Content-Type' => 'text/plain; version=0.0.4'],
        );
    }
}
