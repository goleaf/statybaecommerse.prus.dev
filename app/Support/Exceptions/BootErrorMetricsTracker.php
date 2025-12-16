<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tracks boot error metrics for monitoring and alerting.
 */
final class BootErrorMetricsTracker
{
    public function __construct(
        private readonly BootErrorDetector $detector
    ) {}

    public function track(Throwable $e): void
    {
        if (! config('exception-handling.performance.track_boot_errors', false)) {
            return;
        }

        // This could integrate with monitoring services like Sentry, New Relic, etc.
        // For now, we'll just log the metric
        Log::info('Boot error metric tracked', [
            'exception_type' => get_class($e),
            'error_pattern'  => $this->detector->identifyErrorPattern($e),
            'file_type'      => $this->detector->identifyFileType($e),
        ]);
    }
}
