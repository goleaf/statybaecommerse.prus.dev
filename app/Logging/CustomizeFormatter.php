<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

final class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(new UidProcessor);
        $logger->pushProcessor(new WebProcessor);
    }
}
