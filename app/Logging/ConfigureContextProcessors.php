<?php

declare(strict_types=1);

namespace App\Logging;

use App\Support\Logging\LogContext;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger as MonologLogger;

final class ConfigureContextProcessors
{
    public function __construct(private readonly LogContext $logContext)
    {
    }

    public function __invoke(IlluminateLogger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof MonologLogger) {
            $monolog->pushProcessor(function (array $record): array {
                $context = $this->logContext->toArray();

                if ($context === []) {
                    return $record;
                }

                $record['context'] = array_merge($context, $record['context'] ?? []);
                $record['extra'] = array_merge($context, $record['extra'] ?? []);

                return $record;
            });
        }
    }
}
