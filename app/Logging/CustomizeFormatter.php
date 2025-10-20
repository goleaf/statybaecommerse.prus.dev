<?php

declare(strict_types=1);

namespace App\Logging;

use App\Logging\Processors\KibanaContextProcessor;
use App\Logging\Processors\TraceContextProcessor;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

final class CustomizeFormatter
{
    private const DATE_FORMAT = 'Y-m-d\TH:i:s.vP';

    public function __invoke(IlluminateLogger $logger): void
    {
        $monolog = $logger->getLogger();

        if (! $monolog instanceof Logger) {
            return;
        }

        $monolog->pushProcessor(new TraceContextProcessor);
        $monolog->pushProcessor(new KibanaContextProcessor);

        foreach ($monolog->getHandlers() as $handler) {
            if (method_exists($handler, 'setFormatter')) {
                $handler->setFormatter($this->createFormatter());
            }
        }
    }

    private function createFormatter(): JsonFormatter
    {
        $formatter = new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES, true);
        $formatter->addJsonEncodeOption(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $formatter->setDateFormat(self::DATE_FORMAT);
        $formatter->setMaxNormalizeDepth(5);
        $formatter->includeStacktraces();

        return $formatter;
    }
}
