<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/config',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ]);

    $config->sets([
        LevelSetList::UP_TO_PHP_83,
    ]);

    $config->rule(InlineConstructorDefaultToPropertyRector::class);

    $config->importNames();
    $config->parallel();
};
