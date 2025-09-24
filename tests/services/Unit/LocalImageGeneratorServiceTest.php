<?php

declare(strict_types=1);

use App\Services\Images\LocalImageGeneratorService;
use Tests\TestCase;

uses(TestCase::class);

it('generates webp image or skips if GD missing', function () {
    if (! extension_loaded('gd')) {
        test()->markTestSkipped('GD not available');
    }

    $svc = app(LocalImageGeneratorService::class);
    $path = $svc->generateWebPImage('Hello World', 50, 30);

    expect(file_exists($path))->toBeTrue();
    @unlink($path);
});
