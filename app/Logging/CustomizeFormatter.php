<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

final class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if (method_exists($handler, 'setFormatter')) {
                $handler->setFormatter($this->createFormatter());
            }
        }
    }

    private function createFormatter(): JsonFormatter
    {
        $formatter = new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, true);
        $formatter->includeStacktraces();

        return $formatter;
    }
}
