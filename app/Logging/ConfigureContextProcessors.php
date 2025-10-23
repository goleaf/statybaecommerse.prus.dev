<?php

declare(strict_types=1);

namespace App\Logging;

use App\Support\Logging\LogContext;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger as MonologLogger;
use Monolog\LogRecord;

final class ConfigureContextProcessors
{
    public function __construct(private readonly LogContext $logContext)
    {
    }

    public function __invoke(IlluminateLogger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof MonologLogger) {
            $monolog->pushProcessor(function (mixed $record) {
                $context = $this->logContext->toArray();

                if ($context === []) {
                    return $record;
                }

                if ($record instanceof LogRecord) {
                    return $record->with(
                        context: array_merge($context, $record->context),
                        extra: array_merge($context, $record->extra),
                    );
                }

                if (is_array($record)) {
                    $record['context'] = array_merge($context, $record['context'] ?? []);
                    $record['extra'] = array_merge($context, $record['extra'] ?? []);

                    return $record;
                }

                return $record;
            });
        }
    }
}
