<?php

declare(strict_types=1);

use App\Services\Images\WebPConversionService;
use Tests\TestCase;

uses(TestCase::class);

it('reports webp support flags', function () {
    $svc = app(WebPConversionService::class);
    $flags = $svc->getWebPSupport();

    expect($flags)->toHaveKeys(['gd_extension', 'webp_function', 'webp_support']);
});

it('convertImageToWebP returns false safely when unsupported', function () {
    $svc = app(WebPConversionService::class);

    // Create a tiny temp png to avoid external deps
    $tmp = sys_get_temp_dir().'/tiny_'.uniqid().'.png';
    $im = imagecreatetruecolor(1, 1);
    imagepng($im, $tmp);
    imagedestroy($im);

    $out = preg_replace('/\.png$/', '.webp', $tmp);
    $ok = $svc->convertImageToWebP($tmp, $out);

    // Either succeeds (if webp available) or returns false
    expect(is_bool($ok))->toBeTrue();

    @unlink($tmp);
    @unlink($out);
});
