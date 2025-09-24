<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Filament\Facades\Filament;

$bad = [];
$checked = 0;

foreach (Filament::getPanels() as $panel) {
    foreach ($panel->getResources() as $resourceClass) {
        $pages = $resourceClass::getPages();
        foreach ($pages as $name => $pageClass) {
            $checked++;
            try {
                // This will throw if the page does not initialize static $resource
                $res = $pageClass::getResource();
            } catch (Throwable $e) {
                $bad[] = $pageClass.' :: '.get_class($e).' :: '.$e->getMessage();
            }
        }
    }
}

if ($bad) {
    echo implode("\n", $bad), "\n";
} else {
    echo "OK checked $checked pages\n";
}
