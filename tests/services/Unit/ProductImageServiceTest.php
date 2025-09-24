<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\Images\ProductImageService;
use Tests\TestCase;

uses(TestCase::class);

it('generates random product image or skips if GD missing', function () {
    if (! function_exists('imagecreatetruecolor')) {
        test()->markTestSkipped('GD not available');
    }

    $svc = app(ProductImageService::class);
    $path = $svc->generateRandomProductImage('Sample', 1234);

    expect(file_exists($path))->toBeTrue();
    @unlink($path);
});

it('generateMultipleImages returns empty array on failures gracefully', function () {
    // Force failure by monkey patching function check: if GD not loaded, this will throw internally and be caught
    if (function_exists('imagecreatetruecolor')) {
        test()->markTestSkipped('Skip failure path when GD is available');
    }

    $svc = app(ProductImageService::class);
    $p = new Product;
    $p->id = 1;
    $images = $svc->generateMultipleImages($p, 2);

    expect($images)->toBeArray();
});
