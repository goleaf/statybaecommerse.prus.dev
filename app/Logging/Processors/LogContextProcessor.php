<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use App\Support\Logging\LogContext;
use Monolog\LogRecord;

final class LogContextProcessor
{
    public function __construct(private readonly LogContext $logContext)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        // Pull the latest context snapshot so correlation details stay in sync
        // with the current request or CLI command.
        $context = $this->logContext->toArray();

        if ($context === []) {
            // When no extra context is registered we can short-circuit and
            // avoid triggering Monolog's immutable cloning logic.
            return $record;
        }

        // Merge the shared context with existing record data while ensuring the
        // per-record fields take precedence when keys overlap.
        return $record->with(
            context: array_merge($context, $record->context),
            extra: array_merge($context, $record->extra),
        );
    }
}
