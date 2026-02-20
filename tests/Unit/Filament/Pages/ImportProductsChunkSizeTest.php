<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Pages;

use App\Filament\Pages\Imports\ImportProducts;
use ReflectionMethod;

it('uses a 20-row chunk size for products imports', function (): void {
    $page = new ImportProducts;

    $method = new ReflectionMethod($page, 'getChunkSize');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe(20);
});

it('uses a 300-second import processing timeout for products imports', function (): void {
    $page = new ImportProducts;

    $method = new ReflectionMethod($page, 'getImportProcessingTimeoutSeconds');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe(300);
});
