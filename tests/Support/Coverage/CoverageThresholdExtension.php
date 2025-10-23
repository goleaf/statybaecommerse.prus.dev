<?php

declare(strict_types=1);

namespace Tests\Support\Coverage;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use function extension_loaded;
use function ini_get;
use function str_contains;

final class CoverageThresholdExtension implements Extension
{
    private const PARAMETER_NAME = 'minCoverage';

    private const DEFAULT_THRESHOLD = 70.0;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (! self::coverageDriverAvailable()) {
            return;
        }

        $threshold = self::DEFAULT_THRESHOLD;

        if ($parameters->has(self::PARAMETER_NAME)) {
            $threshold = (float) $parameters->get(self::PARAMETER_NAME);
        }

        $facade->requireCodeCoverageCollection();
        $facade->registerSubscriber(new CoverageThresholdSubscriber($threshold));
    }

    private static function coverageDriverAvailable(): bool
    {
        if (extension_loaded('xdebug')) {
            $mode = (string) ini_get('xdebug.mode');

            return $mode === '' || str_contains($mode, 'coverage');
        }

        if (extension_loaded('pcov')) {
            return true;
        }

        return false;
    }
}
