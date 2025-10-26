<?php

declare(strict_types=1);

namespace App\Logging;

use App\Logging\Processors\LogContextProcessor;
use App\Support\Logging\LogContext;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger as MonologLogger;

final class ConfigureContextProcessors
{
    public function __construct(private readonly LogContext $logContext) {}

    public function __invoke(IlluminateLogger $logger): void
    {
        $monolog = $logger->getLogger();

        if (! $monolog instanceof MonologLogger) {
            return;
        }

        // Delegate the heavy lifting to a dedicated processor so the enrichment
        // logic can be unit tested independently of the logger setup itself.
        $monolog->pushProcessor(new LogContextProcessor($this->logContext));
    }
}
